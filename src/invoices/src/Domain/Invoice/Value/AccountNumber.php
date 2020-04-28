<?php


namespace App\Domain\Invoice\Value;


class AccountNumber
{
    private $accountNumber;

    public function __construct(string $accountNumber, string $countryCode)
    {
        $this->validate($accountNumber, $countryCode);
        $this->accountNumber = $accountNumber;
    }

    public function get(): string
    {
        return $this->accountNumber;
    }

    private function validate(string $accountNumber, string $countryCode)
    {

    }
}