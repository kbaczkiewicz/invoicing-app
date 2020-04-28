<?php


namespace App\Domain\Invoice\Model;


use App\Domain\Validation\Validatable;
use App\Domain\Invoice\Value\VatRate;
use Money\Currency;
use Money\Money;

class Position implements Validatable
{
    private $name;
    private $quantity;
    private $priceNett;
    private $vatRate;

    public function __construct(string $name, float $quantity, Money $priceNett, VatRate $vatRate)
    {
        $this->name = $name;
        $this->quantity = $quantity;
        $this->priceNett = $priceNett;
        $this->vatRate = $vatRate;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'quantity' => $this->quantity,
            'priceNett' => $this->getPriceNett()->jsonSerialize(),
            'unitPriceNett' => $this->getUnitPriceNett()->jsonSerialize(),
            'priceGross' => $this->getPriceGross()->jsonSerialize(),
            'unitPriceGross' => $this->getUnitPriceGross()->jsonSerialize(),
            'vatValue' => $this->getVatValue()->jsonSerialize(),
            'unitVatValue' => $this->getUnitVatValue()->jsonSerialize(),
            'vatRate' => $this->vatRate->get(),
        ];
    }

    public function getPriceNett()
    {
        return new Money(
            ($this->priceNett->getAmount() * $this->quantity),
            $this->priceNett->getCurrency()
        );
    }

    public function getPriceGross()
    {
        return new Money(
            ($this->priceNett->getAmount() * $this->quantity + $this->priceNett->getAmount() * ($this->vatRate->get(
                    ) / 100)),
            $this->priceNett->getCurrency()
        );
    }

    public function getUnitPriceNett()
    {
        return new Money(
            ($this->priceNett->getAmount()),
            $this->priceNett->getCurrency()
        );
    }

    public function getUnitPriceGross()
    {
        return new Money(
            ((int)$this->priceNett->getAmount() + $this->priceNett->getAmount() * ($this->vatRate->get(
                    ) / 100)),
            $this->priceNett->getCurrency()
        );
    }

    public function getVatValue(): Money
    {
        return new Money(
            $this->priceNett->getAmount() * $this->quantity * ($this->vatRate->get() / 100),
            $this->priceNett->getCurrency()
        );
    }

    public function getUnitVatValue()
    {
        return new Money(
            $this->priceNett->getAmount() * ($this->vatRate->get() / 100), $this->priceNett->getCurrency()
        );
    }

    public function getCurrency()
    {
        return $this->priceNett->jsonSerialize()['currency'];
    }

    public static function create(array $data): self
    {
        return new self(
            $data['name'],
            $data['quantity'],
            new Money(
                !empty($data['priceNett']['amount']) ? ($data['priceNett']['amount']) : 0,
                new Currency($data['priceNett']['currency'])
            ),
            new VatRate($data['vatRate'])
        );
    }

    public function validate(): array
    {
        $errors = [];
        if (empty($this->name)) {
            $errors['name'] = 'Name must not be empty';
        }

        if ($this->quantity < 0.0) {
            $errors['quantity'] = 'Quantity must be greater than 0';
        }

        foreach ($this->vatRate->validate() as $field => $vatRateError) {
            $errors['vatRate.'.$field] = $vatRateError;
        }

        return $errors;
    }
}
