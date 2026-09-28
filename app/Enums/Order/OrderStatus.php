<?php

namespace App\Enums\Order;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case Paid = 'paid';
    case Fulfilled = 'fulfilled';
}
