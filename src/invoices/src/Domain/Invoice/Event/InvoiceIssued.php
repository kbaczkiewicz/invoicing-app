<?php


namespace App\Domain\Invoice\Event;


use App\Domain\Invoice\Model\Contact;
use App\Domain\Invoice\Model\PaymentType;
use App\Domain\Invoice\Model\Position;
use App\Domain\Validation\Validatable;
use App\Domain\Invoice\Value\Status\Issued;

class InvoiceIssued implements Event, Validatable
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
        string $number,
        Contact $issuer,
        Contact $receiver,
        array $products,
        PaymentType $paymentType,
        \DateTime $dateLastUpdated,
        \DateTime $paymentDate,
        \DateTime $dateIssued
    ) {
        $this->number = $number;
        $this->issuer = $issuer;
        $this->receiver = $receiver;
        $this->products = $products;
        $this->paymentType = $paymentType;
        $this->dateLastUpdated = $dateLastUpdated;
        $this->paymentDate = $paymentDate;
        $this->dateIssued = $dateIssued;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getIssuer(): Contact
    {
        return $this->issuer;
    }

    public function getReceiver(): Contact
    {
        return $this->receiver;
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function getPaymentType(): PaymentType
    {
        return $this->paymentType;
    }

    public function getDateLastUpdated(): \DateTime
    {
        return $this->dateLastUpdated;
    }

    public function getPaymentDate(): \DateTime
    {
        return $this->paymentDate;
    }

    public function getStatus()
    {
        return new Issued();
    }

    public function getDateIssued(): \DateTime
    {
        return $this->dateIssued;
    }

    public function jsonSerialize()
    {
        return [
            'number' => $this->number,
            'issuer' => $this->issuer->jsonSerialize(),
            'receiver' => $this->receiver->jsonSerialize(),
            'products' => array_map(
                function (Position $product) {
                    return $product->jsonSerialize();
                },
                $this->products
            ),
            'dateLastUpdated' => $this->getDateLastUpdated()->format('Y-m-d H:i:s'),
            'paymentType' => $this->paymentType->jsonSerialize(),
            'status' => $this->getStatus()->get(),
            'paymentDate' => $this->paymentDate->format('Y-m-d'),
            'dateIssued' => $this->dateIssued->format('Y-m-d'),
        ];
    }

    public function validate(): array
    {
        $errors = [];
        if (empty($this->number)) {
            $errors['number'] = 'Number must not be empty';
        }
        if ($this->dateIssued > $this->paymentDate) {
            $errors['dateIssued'] = 'Issued date is illegal';
        }

        if ($this->dateLastUpdated < $this->dateIssued) {
            $errors['dateLastUpdated'] = 'Last updated date is illegal';
        }

        foreach ($this->issuer->validate() as $field => $issuerError) {
            $errors['issuer.' . $field] = $issuerError;
        }

        foreach ($this->receiver->validate() as $field => $receiverError) {
            $errors['receiver.' . $field] = $receiverError;
        }

        foreach ($this->products as $product) {
            $i = 0;
            foreach ($product->validate() as $field => $productError) {
                $errors['products.'.$i.'.'.$field] = $productError;
            }
        }

        return $errors;
    }
}