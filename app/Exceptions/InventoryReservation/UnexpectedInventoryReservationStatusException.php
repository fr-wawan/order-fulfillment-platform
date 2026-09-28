<?php

namespace App\Exceptions\InventoryReservation;

use App\Models\InventoryReservation;
use Exception;

class UnexpectedInventoryReservationStatusException extends Exception
{
    public function __construct(InventoryReservation $reservation)
    {
        parent::__construct(
            "Inventory reservation {$reservation->id} must be reserved before fulfillment."
        );
    }
}
