<?php


namespace App\Domain\Invoice\Value;


use App\Domain\Validation\Validatable;

class VatId implements Validatable
{
    private $vatId;
    private $countryCode;

    public function __construct(string $vatId, string $countryCode)
    {
        $this->vatId = $vatId;
        $this->countryCode = $countryCode;
    }

    public function validate(): array
    {
        $errors = [];
        switch($this->countryCode) {
            case 'pl':
                if (!is_numeric($this->vatId)) {
                    $errors['vatId'] = 'Numbers allowed only';
                }

                if (empty($this->vatId)) {
                    $errors['vatId'] = 'Vat ID must not be empty';
                }

                if (strlen($this->vatId) < 8) {
                    $errors['vatId'] = 'Vat ID is too short';
                }
        }

        return $errors;
    }

    public function get(): string
    {
        return $this->vatId;
    }

    public static function create(array $data): self
    {
        return new self($data['vatId'], $data['countryCode']);
    }
}