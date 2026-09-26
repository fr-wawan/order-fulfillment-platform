<?php

namespace App\Enums\InventoryReservation;

enum InventoryReservationStatus: string
{
    case Reserved = 'reserved';
    case Released = 'released';
}
