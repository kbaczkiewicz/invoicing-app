<?php


namespace App\Domain\Invoice\Model;


use App\Domain\AggregateRoot;
use App\Domain\Invoice\Event\InvoiceCreated;
use App\Domain\Invoice\Event\InvoiceDraftSaved;
use App\Domain\Invoice\Event\InvoiceIssued;
use App\Domain\Invoice\Event\InvoiceStatusChanged;
use App\Domain\Invoice\Value\Status\Status;
use App\Domain\Validation\ValidationException;
use Money\Currency;
use Money\Money;
use Ramsey\Uuid\Uuid;

class Invoice extends AggregateRoot
{
    private $ownerId;
    private $number;
    private $issuer;
    private $receiver;
    private $products = [];
    private $paymentType;
    private $dateCreated;
    private $dateLastUpdated;
    private $paymentDate;
    private $dateIssued;
    private $status;

    public static function create(string $ownerId)
    {
        $event = new InvoiceCreated(
            $ownerId,
            Uuid::uuid4()->toString(),
            new \DateTime('now'),
            new \DateTime('now + 10 days')
        );

        $model = new self();
        $model->apply($event);
        $model->record($event);

        return $model;
    }

    public static function getStatus(string $status)
    {
        if (array_key_exists($status, Status::STATUS_MAP)) {
            $className = Status::STATUS_MAP[$status];

            return new $className();
        }

        throw new \InvalidArgumentException('Status does not exist');
    }

    public function saveDraft(
        ?string $number,
        ?Contact $issuer,
        ?Contact $receiver,
        array $products,
        ?PaymentType $paymentType,
        ?\DateTime $paymentDate,
        ?\DateTime $dateIssued
    ) {
        $event = new InvoiceDraftSaved(
            new \DateTime(),
            $products,
            $number,
            $issuer,
            $receiver,
            $paymentType,
            $paymentDate,
            $dateIssued
        );
        if (!empty($event->validate())) {
            throw new ValidationException(json_encode($event->validate()));
        }

        $this->apply($event);
        $this->record($event);
    }

    public function isOwedBy(string $ownerId): bool
    {
        return $this->ownerId === $ownerId;
    }

    public function issue(
        string $number,
        Contact $issuer,
        Contact $receiver,
        array $products,
        PaymentType $paymentType,
        \DateTime $paymentDate,
        \DateTime $dateIssued
    ) {
        $event = new InvoiceIssued(
            $number,
            $issuer,
            $receiver,
            $products,
            $paymentType,
            new \DateTime(),
            $paymentDate,
            $dateIssued
        );
        if (!empty($event->validate())) {
            throw new ValidationException(json_encode($event->validate()));
        }

        $this->apply($event);
        $this->record($event);
    }

    public function changeStatus(Status $status)
    {
        $event = new InvoiceStatusChanged($status);
        $this->apply($event);
        $this->record($event);
    }

    public function getTotalPriceNett(): ?Money
    {
        if (empty($this->products)) {
            return null;
        }

        $sum = array_sum(
            array_map(
                function (Position $product) {
                    return $product->getPriceNett()->getAmount();
                },
                $this->products
            )
        );

        return new Money($sum, $this->products[0]->getPriceNett()->getCurrency());
    }

    public function getTotalPriceGross(): ?Money
    {
        if (empty($this->products)) {
            return null;
        }

        $sum = array_sum(
            array_map(
                function (Position $product) {
                    return $product->getPriceGross()->getAmount();
                },
                $this->products
            )
        );

        return new Money($sum, $this->products[0]->getPriceGross()->getCurrency());
    }

    protected function applyInvoiceCreated(InvoiceCreated $event)
    {
        $this->id = $event->getInvoiceId();
        $this->ownerId = $event->getOwnerId();
        $this->status = $event->getStatus();
        $this->dateCreated = $event->getDateCreated();
    }

    protected function applyInvoiceDraftSaved(InvoiceDraftSaved $event)
    {
        $this->number = $event->getNumber();
        $this->issuer = $event->getIssuer();
        $this->receiver = $event->getReceiver();
        $this->products = $event->getProducts();
        $this->paymentType = $event->getPaymentType();
        $this->dateLastUpdated = $event->getDateLastUpdated();
        $this->paymentDate = $event->getPaymentDate();
        $this->status = $event->getStatus();
        $this->dateIssued = $event->getDateIssued();
    }

    protected function applyInvoiceIssued(InvoiceIssued $event)
    {
        $this->number = $event->getNumber();
        $this->issuer = $event->getIssuer();
        $this->receiver = $event->getReceiver();
        $this->products = $event->getProducts();
        $this->paymentType = $event->getPaymentType();
        $this->dateLastUpdated = $event->getDateLastUpdated();
        $this->paymentDate = $event->getPaymentDate();
        $this->status = $event->getStatus();
        $this->dateIssued = $event->getDateIssued();
    }

    protected function applyInvoiceStatusChanged(InvoiceStatusChanged $event)
    {
        $this->status = $event->getStatus();
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'issuer' => $this->issuer ? $this->issuer->jsonSerialize() : null,
            'receiver' => $this->receiver ? $this->receiver->jsonSerialize() : null,
            'products' => array_map(
                function (Position $product) {
                    return $product->jsonSerialize();
                },
                $this->products
            ),
            'paymentType' => $this->paymentType ? $this->paymentType->jsonSerialize() : null,
            'dateCreated' => $this->dateCreated->format('Y-m-d H:i:s'),
            'paymentDate' => $this->paymentDate ? $this->paymentDate->format('Y-m-d') : null,
            'dateIssued' => $this->dateIssued ? $this->dateIssued->format('Y-m-d') : null,
            'status' => $this->status->get(),
            'totalPriceNett' => $this->getTotalPriceNett() ? $this->getTotalPriceNett()->jsonSerialize() : null,
            'totalPriceGross' => $this->getTotalPriceGross() ? $this->getTotalPriceGross()->jsonSerialize() : null,
            'currency' => isset($this->products[0]) ? $this->products[0]->getCurrency() : null,
        ];
    }

    public function getAggregateName(): string
    {
        return Invoice::class;
    }
}

