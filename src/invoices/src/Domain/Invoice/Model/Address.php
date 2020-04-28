<?php


namespace App\Domain\Invoice\Model;


use App\Domain\Entity;
use App\Domain\Validation\Validatable;
use App\Domain\Invoice\Value\ISOCountryCode;
use App\Domain\Invoice\Value\PostCode;

class Address implements Validatable
{
    private $country;
    private $city;
    private $postCode;
    private $streetName;
    private $buildingNumber;
    private $apartmentNumber;

    public function __construct(
        Country $country,
        string $city,
        PostCode $postCode,
        string $streetName,
        string $buildingNumber,
        ?string $apartmentNumber
    )
    {
        $this->country = $country;
        $this->city = $city;
        $this->postCode = $postCode;
        $this->streetName = $streetName;
        $this->buildingNumber = $buildingNumber;
        $this->apartmentNumber = $apartmentNumber;
    }

    public function jsonSerialize()
    {
        return [
            'country' => $this->country->jsonSerialize(),
            'city' => $this->city,
            'postCode' => $this->postCode->get(),
            'streetName' => $this->streetName,
            'buildingNumber' => $this->buildingNumber,
            'apartmentNumber' => $this->apartmentNumber
        ];
    }

    public static function create(array $data)
    {
        return new self(
            new Country(
                $data['country']['name'],
                new ISOCountryCode($data['country']['isoCode'])
            ),
            $data['city'],
            new PostCode($data['postCode'], $data['country']['isoCode']),
            $data['streetName'],
            $data['buildingNumber'],
            $data['apartmentNumber'] ?? null
        );
    }

    public function validate(): array
    {
        $errors = [];
        if (empty($this->city)) {
            $errors['city'] = 'City must not be empty';
        }

        if (empty($this->streetName)) {
            $errors['streetName'] = 'Street name must not be empty';
        }

        if (empty($this->buildingNumber)) {
            $errors['buildingNumber'] = 'Building number must not be empty';
        }

        foreach ($this->country->validate() as $field => $countryError) {
            $errors['country.' . $field] = $countryError;
        }

        foreach ($this->postCode->validate() as $field => $postCodeError) {
            $errors['postCode.' . $field] = $postCodeError;
        }

        return $errors;
    }
}