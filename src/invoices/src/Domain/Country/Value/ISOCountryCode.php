<?php


namespace App\Domain\Country\Value;


use App\Domain\Validation\Validatable;

class ISOCountryCode implements Validatable
{
    private $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function get(): string
    {
        return $this->code;
    }

    public function validate(): array
    {
        $errors = [];
        if (strlen($this->code) > 3) {
            $errors['code'] = 'ISO Code is too long';
        } else if (strlen($this->code) < 2) {
            $errors['code'] = 'ISO Code is too short';
        }

        return $errors;
    }
}