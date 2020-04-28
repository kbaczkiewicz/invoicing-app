<?php


namespace App\Domain\Invoice\Event;


use App\Domain\Invoice\Value\Status\Created;
use App\Domain\Invoice\Value\Status\Status;

class InvoiceCreated implements Event
{
    private $ownerId;
    private $invoiceId;
    private $dateCreated;
    private $paymentDate;

    public function __construct(
        string $ownerId,
        string $invoiceId,
        \DateTime $dateCreated,
        \DateTime $paymentDate
    )
    {
        $this->ownerId = $ownerId;
        $this->invoiceId = $invoiceId;
        $this->dateCreated = $dateCreated;
        $this->paymentDate = $paymentDate;
    }

    public function getOwnerId(): string
    {
        return $this->ownerId;
    }

    public function getInvoiceId(): string
    {
        return $this->invoiceId;
    }

    public function getStatus(): Status
    {
        return new Created();
    }

    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    public function getPaymentDate(): \DateTime
    {
        return $this->paymentDate;
    }

    public function jsonSerialize()
    {
        return [
            'invoiceId' => $this->getInvoiceId(),
            'ownerId' => $this->ownerId,
            'status' => $this->getStatus(),
            'dateCreated' => $this->getDateCreated(),
            'paymentDate' => $this->getPaymentDate()
        ];
    }

    public function validate(): array
    {
        $errors = [];
        if (empty($this->ownerId)) {
            $errors['ownerId'] = 'Owner id not set';
        }

        if (empty($this->invoiceId)) {
            $errors['invoiceId'] = 'Invoice id not set';
        }

        return $errors;
    }
}