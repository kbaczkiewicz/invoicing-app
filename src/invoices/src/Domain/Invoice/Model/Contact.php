<?php


namespace App\Domain\Invoice\Model;


use App\Domain\Validation\Validatable;
use App\Domain\Invoice\Value\AccountNumber;
use App\Domain\Invoice\Value\VatId;

class Contact implements Validatable
{
    private $name;
    private $vatId;
    private $billingAddress;
    private $accountNumber;

    public function __construct(
        string $name,
        VatId $vatId,
        Address $billingAddress,
        AccountNumber $accountNumber
    ) {
        $this->name = $name;
        $this->vatId = $vatId;
        $this->billingAddress = $billingAddress;
        $this->accountNumber = $accountNumber;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'vatId' => $this->vatId->get(),
            'billingAddress' => $this->billingAddress->jsonSerialize(),
            'accountNumber' => $this->accountNumber->get(),
        ];
    }

    public static function create(array $data): self
    {
        $country = $data['billingAddress']['country'];

        return new self(
            $data['name'],
            new VatId($data['vatId'] ?? '', $country['isoCode']),
            Address::create($data['billingAddress']),
            new AccountNumber(
                $data['accountNumber'],
                $country['isoCode']
            )
        );
    }

    public function validate(): array
    {
        $errors = [];
        if(empty($this->name)) {
            $errors['name'] = 'Name must not be empty';
        }

        foreach ($this->billingAddress->validate() as $field => $addressError) {
            $errors['address.' . $field] = $addressError;
        }

        foreach ($this->vatId->validate() as $field => $vatIdError) {
            $errors['vatId.' . $field] = $vatIdError;
        }

        return $errors;
    }
}