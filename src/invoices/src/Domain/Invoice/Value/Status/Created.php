<?php


namespace App\Domain\Invoice\Value\Status;


class Created implements Status
{

    public function get(): string
    {
        return 'Created';
    }
}