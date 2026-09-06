<?php

namespace App\Enums\Order;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Cancelled = 'cancelled';
}
