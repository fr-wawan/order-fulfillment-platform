<?php

namespace App\Enums\Payment;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case RefundQueued = 'refund_queued';
    case RefundSubmitting = 'refund_submitting';
    case RefundPending = 'refund_pending';
    case RefundFailed = 'refund_failed';
    case Refunded = 'refunded';
}
