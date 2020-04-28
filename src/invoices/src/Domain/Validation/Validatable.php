<?php


namespace App\Domain\Validation;


interface Validatable
{
    public function validate(): array;
}