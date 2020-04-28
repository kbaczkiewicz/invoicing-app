<?php


namespace App\Domain\Invoice\Value\Status;


class Overdue implements Status
{

    public function get(): string
    {
        return 'Overdue';
    }
}