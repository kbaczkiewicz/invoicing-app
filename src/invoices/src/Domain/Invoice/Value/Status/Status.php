<?php


namespace App\Domain\Invoice\Value\Status;


interface Status
{
    public function get(): string;
    public const STATUS_MAP = [
        'Created' => Created::class,
        'Draft' => Draft::class,
        'Issued' => Issued::class,
        'Overdue' => Overdue::class,
        'Paid' => Paid::class
    ];
}