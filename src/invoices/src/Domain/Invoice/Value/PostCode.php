<?php


namespace App\Domain\Invoice\Value;


class PostCode
{
    private $postCode;
    private $countryCode;

    public function __construct(string $postCode, string $countryCode)
    {
        $this->postCode = $postCode;
        $this->countryCode = $countryCode;
    }

    public function get(): string
    {
        return $this->postCode;
    }

    public function validate(): array
    {
        $errors = [];
        switch ($this->countryCode) {
            case 'PL':
                if (false === preg_match('/[0-9]{2}-[0-9]{3}/', $this->postCode)) {
                    $errors['postCode'] = 'Invalid post code';
                }
        }

        return $errors;
    }
}