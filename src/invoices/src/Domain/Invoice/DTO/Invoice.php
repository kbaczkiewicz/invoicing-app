<?php


namespace App\Domain\Invoice\DTO;


class Invoice
{
    private $number;
    private $issuer;
    private $receiver;
    private $products;
    private $paymentType;
    private $paymentDate;
    private $dateIssued;

    public static function create(array $data)
    {
        $request = new self();
        $request->number = $data['number'];
        $request->issuer = $data['issuer'];
        $request->receiver = $data['receiver'];
        $request->products = $data['products'];
        $request->paymentType = $data['paymentType'];
        $request->paymentDate = $data['paymentDate'];
        $request->dateIssued = $data['dateIssued'];

        return $request;
    }

    public static function createDraft(array $data)
    {
        $request = new self();
        $request->number = $data['number'] ?? null;
        $request->issuer = $data['issuer'] ?? null;
        $request->receiver = $data['receiver'] ?? null;
        $request->products = $data['products'] ?? null;
        $request->paymentType = $data['paymentType'] ?? null;
        $request->paymentDate = $data['paymentDate'] ?? null;
        $request->dateIssued = $data['dateIssued'] ?? null;

        return $request;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function getIssuer()
    {
        return $this->issuer;
    }

    public function getReceiver()
    {
        return $this->receiver;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function getPaymentType()
    {
        return $this->paymentType;
    }

    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    public function getDateIssued()
    {
        return $this->dateIssued;
    }
}