<?php


namespace App\Domain\Invoice\Value\Status;


class Paid implements Status
{

    public function get(): string
    {
        return 'Paid';
    }
}