<?php


namespace App\Domain\Invoice\Value\Status;


class Issued implements Status
{
    public function get(): string
    {
        return 'Issued';
    }
}