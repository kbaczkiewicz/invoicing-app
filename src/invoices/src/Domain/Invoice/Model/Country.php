<?php


namespace App\Domain\Invoice\Model;


use App\Domain\Validation\Validatable;
use App\Domain\Invoice\Value\ISOCountryCode;

class Country implements Validatable
{
    private $name;
    private $isoCode;

    public function __construct(string $name, ISOCountryCode $isoCode)
    {
        $this->name = $name;
        $this->isoCode = $isoCode;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'isoCode' => $this->isoCode->get(),
        ];
    }

    public function validate(): array
    {
        $errors = [];
        if (empty($this->name)) {
            $errors['name'] = 'Name must not be empty';
        }

        foreach ($this->isoCode->validate() as $field => $isoCodeError) {
            $errors['isoCode.' . $field] = $isoCodeError;
        }

        return $errors;
    }
}