<?php

namespace App\Domain\Invoice\Event;

use App\Domain\Invoice\Model\Contact;
use App\Domain\Invoice\Model\PaymentType;
use App\Domain\Invoice\Model\Position;
use App\Domain\Invoice\Value\Status\Draft;
use App\Domain\Invoice\Value\Status\Status;

class InvoiceDraftSaved implements Event
{
    private $number;
    private $issuer;
    private $receiver;
    private $products = [];
    private $paymentType;
    private $dateLastUpdated;
    private $paymentDate;
    private $dateIssued;

    public function __construct(
        \DateTime $dateLastUpdated,
        array $products,
        ?string $number,
        ?Contact $issuer,
        ?Contact $receiver,
        ?PaymentType $paymentType,
        ?\DateTime $paymentDate,
        ?\DateTime $issuedDate
    ) {
        $this->dateLastUpdated = $dateLastUpdated;
        $this->products = $products;
        $this->number = $number;
        $this->issuer = $issuer;
        $this->receiver = $receiver;
        $this->paymentType = $paymentType;
        $this->paymentDate = $paymentDate;
        $this->dateIssued = $issuedDate;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function getIssuer(): ?Contact
    {
        return $this->issuer;
    }

    public function getReceiver(): ?Contact
    {
        return $this->receiver;
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function getPaymentType(): ?PaymentType
    {
        return $this->paymentType;
    }

    public function getDateLastUpdated(): \DateTime
    {
        return $this->dateLastUpdated;
    }

    public function getPaymentDate(): ?\DateTime
    {
        return $this->paymentDate;
    }

    public function getStatus(): Status
    {
        return new Draft();
    }

    public function getDateIssued(): ?\DateTime
    {
        return $this->dateIssued;
    }

    public function jsonSerialize()
    {
        return [
            'number' => $this->number,
            'issuer' => null !== $this->issuer && empty($this->issuer->validate()) ? $this->issuer : null,
            'receiver' => null !== $this->receiver && empty($this->receiver->validate()) ? $this->receiver : null,
            'products' => array_filter(
                array_map(
                    function (Position $product) {
                        return $product->validate() ? $product->jsonSerialize() : null;
                    },
                    $this->products
                )
            ),
            'status' => $this->getStatus()->get(),
            'paymentType' => $this->paymentType ? $this->paymentType->jsonSerialize() : null,
            'paymentDate' => $this->paymentDate ? $this->paymentDate->format('Y-m-d') : null,
            'dateLastUpdated' => $this->dateLastUpdated->format('Y-m-d H:i:s'),
            'dateIssued' => $this->dateIssued ? $this->dateIssued->format('Y-m-d') : null,
        ];
    }

    public function validate(): array
    {
        $errors = [];
        if ($this->dateLastUpdated < $this->dateIssued) {
            $errors['dateLastUpdated'] = 'Last updated date is illegal';
        }

        return $errors;
    }
}