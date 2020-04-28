<?php


namespace App\Domain\Country\Value;


use App\Domain\Validation\Validatable;

class VatRate implements Validatable
{
    private $rate;

    public function __construct(float $rate)
    {
        $this->rate = $rate;
    }

    public function get(): float
    {
        return $this->rate;
    }

    public function validate(): array
    {
        $errors = [];
        if ($this->rate < 0.0) {
            $errors['rate'] = "Vat rate cannot be less than zero";
        }

        return $errors;
    }
}