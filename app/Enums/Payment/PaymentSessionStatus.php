<?php

namespace App\Enums\Payment;

enum PaymentSessionStatus: string
{
    case Initiating = 'initiating';
    case Ready = 'ready';
}
