<?php

namespace App\Enums\Warehouse;

enum WarehouseStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
