<?php


namespace App\Domain\Invoice\Value\Status;


class Draft implements Status
{

    public function get(): string
    {
        return 'Draft';
    }
}