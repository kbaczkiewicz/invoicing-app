<?php

namespace App\Domain\Invoice\Service;

use App\Application\Event\Store;
use App\Domain\Invoice\DTO\Invoice as InvoiceRequest;
use App\Domain\Invoice\Model\Contact;
use App\Domain\Invoice\Model\Invoice;
use App\Domain\Invoice\Model\PaymentType;
use App\Domain\Invoice\Model\Position;
use App\Domain\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;

class InvoiceService
{
    private $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function get(string $invoiceId, string $ownerId): array
    {
        if (null === $invoiceId) {
            return new JsonResponse(['messages' => ['Invoice id not provided']], JsonResponse::HTTP_BAD_REQUEST);
        }

        $invoice = $this->store->getForAggregate(Invoice::class, $invoiceId);

        return $invoice->jsonSerialize();
    }

    public function getAll(string $ownerId)
    {
        $store = $this->store;
        $invoices = $store->getAllForAggregate(Invoice::class);

        return array_filter(
            array_map(
                function (Invoice $invoice) use ($ownerId) {
                    if ($invoice->isOwedBy($ownerId)) {
                        return $invoice->jsonSerialize();
                    }

                    return null;
                },
                $invoices
            )
        );
    }

    public function create(string $ownerId)
    {
        $invoice = Invoice::create($ownerId);
        $this->store->persist($invoice, 1); //@todo: versioning

        return ['invoiceId' => $invoice->getId()];
    }

    public function saveDraft(string $invoiceId, InvoiceRequest $request)
    {
        try {
            $invoice = $this->store->getForAggregate(Invoice::class, $invoiceId);
            if (null === $invoice) {
                return ['message' => 'Invoice not found'];
            }

            $invoice->saveDraft(
                $request->getNumber(),
                $request->getIssuer() ? Contact::create($request->getIssuer()) : null,
                $request->getReceiver() ? Contact::create($request->getReceiver()) : null,
                array_map(
                    function (array $productData) {
                        return Position::create($productData);
                    },
                    $request->getProducts()
                ),
                $request->getPaymentType() ? new PaymentType(
                    $request->getPaymentType()
                ) : null,
                $request->getPaymentDate() ? \DateTime::createFromFormat(
                    'Y-m-d',
                    $request->getPaymentDate()
                ) : null,
                $request->getDateIssued() ? \DateTime::createFromFormat(
                    'Y-m-d',
                    $request->getDateIssued()
                ) : null
            );

            $this->store->persist($invoice, 1); //@todo versioning

            return [];
        } catch (ValidationException $e) {
            return json_decode($e->getMessage(), true);
        }
    }

    public function issue(string $invoiceId, InvoiceRequest $request)
    {
        try {
            $invoice = $this->store->getForAggregate(Invoice::class, $invoiceId);
            $invoice->issue(
                $request->getNumber(),
                $request->getIssuer() ? Contact::create($request->getIssuer()) : null,
                $request->getReceiver() ? Contact::create($request->getReceiver()) : null,
                array_map(
                    function (array $productData) {
                        return Position::create($productData);
                    },
                    $request->getProducts()
                ),
                $request->getPaymentType() ? new PaymentType(
                    $request->getPaymentType()
                ) : null,
                $request->getPaymentDate(),
                $request->getDateIssued()
            );

            $this->store->persist(
                $invoice,
                1 //@todo versioning
            );

            return [];
        } catch (ValidationException $e) {
            return ['errors' => json_decode($e->getMessage(), true)];
        }
    }

    public function changeStatus(string $invoiceId, string $status)
    {
        $invoice = $this->store->getForAggregate(Invoice::class, $invoiceId);
        $invoice->changeStatus(Invoice::getStatus($status));
        $this->store->persist($invoice, 1); //@todo versioning
    }
}