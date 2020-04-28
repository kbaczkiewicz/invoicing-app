<?php


namespace App\Domain\Invoice\Event;


use App\Domain\Invoice\Value\Status\Status;

class InvoiceStatusChanged implements Event
{

    private $status;

    public function __construct(Status $status)
    {
        $this->status = $status;
    }

    public function jsonSerialize()
    {
        return ['status' => $this->status->get()];
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}