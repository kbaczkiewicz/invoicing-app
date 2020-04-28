<?php


namespace App\Application\Event;


use App\Application\Entity\Event;
use App\Domain\AggregateRoot;
use App\Domain\Invoice\Event\Event as DomainEvent;
use App\Domain\Invoice\Event\InvoiceCreated;
use App\Domain\Invoice\Event\InvoiceDraftSaved;
use App\Domain\Invoice\Event\InvoiceIssued;
use App\Domain\Invoice\Event\InvoiceStatusChanged;
use App\Domain\Invoice\Model\Contact;
use App\Domain\Invoice\Model\Invoice;
use App\Domain\Invoice\Model\PaymentType;
use App\Domain\Invoice\Model\Position;
use Doctrine\ORM\EntityManagerInterface;

class Store
{
    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Event::class);
    }

    public function persist(AggregateRoot $aggregate, int $version)
    {
        /** @var DomainEvent $domainEvent */
        foreach ($aggregate->getRecordedEvents() as $domainEvent) {
            $event = new Event();
            $event->setAggregateId($aggregate->getId());
            $event->setAggregateClass($aggregate->getAggregateName());
            $event->setEvent($domainEvent->jsonSerialize());
            $event->setEventType(get_class($domainEvent));
            $event->setVersion($version);
            $event->setDateCreated(new \DateTime());
            $this->entityManager->persist($event);
        }

        $this->entityManager->flush();
    }

    public function getAllForAggregate(string $aggregate): array
    {
        if (false === class_exists($aggregate)) {
            throw new \LogicException(sprintf('Class %s does not exist', $aggregate));
        }

        $connection = $this->entityManager->getConnection();
        $aggregateIds = $connection->executeQuery(
            'SELECT aggregate_id FROM event_store WHERE aggregate_class = :aggregate_class GROUP BY aggregate_id',
            [':aggregate_class' => $aggregate]
        );

        $aggregates = [];

        /** @var Event $event */
        foreach ($aggregateIds as $aggregateId) {
            $domainEvents = [];
            $events = $this->repository->findBy(['aggregateId' => $aggregateId], ['dateCreated' => 'asc']);
            foreach ($events as $event) {
                $domainEvents[] = $this->handleEvent($event);
            }

            $aggregates[] = Invoice::replay($domainEvents);
        }

        return $aggregates;
    }

    public function getForAggregate(string $aggregate, string $aggregateId): ?AggregateRoot
    {
        if (false === class_exists($aggregate)) {
            throw new \LogicException(sprintf('Class %s does not exist', $aggregate));
        }

        $domainEvents = [];
        $events = $this->repository->findBy(
            ['aggregateId' => $aggregateId, 'aggregateClass' => $aggregate],
            ['dateCreated' => 'desc']
        );
        if (empty($events)) {
            return null;
        }
        /** @var Event $event */
        foreach ($events as $event) {
            $domainEvents[] = $this->handleEvent($event);
        }

        return Invoice::replay($domainEvents);
    }

    private function handleEvent(Event $event): DomainEvent
    {
        if (class_exists($event->getEventType())) {
            $eventFQN = explode('\\', $event->getEventType());
            $eventClassName = end($eventFQN);
            if (method_exists(self::class, 'create'.$eventClassName)) {
                return $this->{'create'.$eventClassName}($event);
            }
        }

        throw new \InvalidArgumentException(sprintf('Event %s does not exist', $event->getEventType()));
    }

    private function createInvoiceCreated(Event $event): InvoiceCreated
    {
        $data = $event->getEvent();

        return new InvoiceCreated(
            $data['ownerId'],
            $data['invoiceId'],
            new \DateTime($data['dateCreated']['date'], new \DateTimeZone($data['dateCreated']['timezone'])),
            new \DateTime($data['paymentDate']['date'], new \DateTimeZone($data['paymentDate']['timezone']))
        );
    }

    private function createInvoiceDraftSaved(Event $event): InvoiceDraftSaved
    {
        $data = $event->getEvent();

        return new InvoiceDraftSaved(
            \DateTime::createFromFormat('Y-m-d H:i:s', $data['dateLastUpdated']),
            array_map(
                function (array $product) {
                    return Position::create($product);
                },
                $data['products']
            ),
            $data['number'],
            isset($data['issuer']) ? Contact::create($data['issuer']) : null,
            isset($data['receiver']) ? Contact::create($data['receiver']) : null,
            isset($data['paymentType'])
                ? new PaymentType($data['paymentType']['name'])
                : null,
            isset($data['paymentDate']) ? \DateTime::createFromFormat('Y-m-d', $data['paymentDate']) : null,
            $data['dateIssued'] ? \DateTime::createFromFormat(
                'Y-m-d',
                $data['dateIssued']
            ) : null
        );
    }

    private function createInvoiceIssued(Event $event)
    {
        $data = $event->getEvent();
        return new InvoiceIssued(
            $data['number'],
            Contact::create($data['issuer']),
            Contact::create($data['receiver']),
            array_map(
                function (array $product) {
                    return Position::create($product);
                },
                $data['products']
            ),
            new PaymentType($data['paymentType']['name']),
            \DateTime::createFromFormat('Y-m-d H:i:s', $data['dateLastUpdated']),
            \DateTime::createFromFormat('Y-m-d', $data['paymentDate']),
            \DateTime::createFromFormat('Y-m-d', $data['dateIssued'])
        );
    }

    private function createInvoiceStatusChanged(Event $event)
    {
        $data = $event->getEvent();

        return new InvoiceStatusChanged(Invoice::getStatus($data['status']));
    }
}