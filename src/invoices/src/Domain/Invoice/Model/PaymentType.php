<?php


namespace App\Domain\Invoice\Model;


class PaymentType
{
    private $id;
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name
        ];
    }
}