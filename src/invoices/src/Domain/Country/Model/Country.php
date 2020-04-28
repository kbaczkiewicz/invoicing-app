<?php


namespace App\Domain\Country\Model;


use App\Domain\Country\Value\ISOCountryCode;
use App\Domain\Country\Value\VatRate;
use App\Domain\Validation\Validatable;

class Country implements \JsonSerializable, Validatable
{
    private $id;
    private $name;
    private $isoCode;
    private $currency;
    private $ownerId;

    public function __construct(string $id, string $name, ISOCountryCode $isoCode, string $currency, string $ownerId)
    {
        $this->id = $id;
        $this->name = $name;
        $this->isoCode = $isoCode;
        $this->currency = $currency;
        $this->ownerId = $ownerId;
    }

    public static function create(array $data)
    {
        return new self(
            $data['id'],
            $data['name'],
            new ISOCountryCode($data['isoCode']),
            $data['currency'],
            $data['ownerId']
        );
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'isoCode' => $this->isoCode->get(),
            'currency' => $this->currency,
            'ownerId' => $this->ownerId,
        ];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getIsoCode(): ISOCountryCode
    {
        return $this->isoCode;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getOwnerId()
    {
        return $this->ownerId;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function validate(): array
    {
        $errors = [];
        if ('' === $this->name) {
            $errors['name'] = 'Name must not be empty';
        }

        return $errors;
    }
}