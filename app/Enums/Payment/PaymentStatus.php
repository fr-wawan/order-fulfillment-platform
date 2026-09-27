<?php

namespace App\Enums\Payment;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case RefundPending = 'refund_pending';
    case Refunded = 'refunded';
}
