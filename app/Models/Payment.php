<?php

namespace App\Models;

use App\Enums\Payment\PaymentSessionStatus;
use App\Enums\Payment\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Override;

/**
 * @property int $id
 * @property int $order_id
 * @property string|null $provider_payment_id
 * @property int $amount
 * @property \Carbon\CarbonImmutable|null $paid_at
 * @property string|null $provider_session_id
 * @property string|null $checkout_url
 * @property PaymentStatus $status
 * @property PaymentSessionStatus $session_status
 * @property string|null $provider_payment_request_id
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Order $order
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCheckoutUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereProviderPaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereProviderPaymentRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereProviderSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereSessionStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Guarded(['id'])]
class Payment extends Model
{
    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'session_status' => PaymentSessionStatus::class,
            'paid_at' => 'datetime'
        ];
    }

    public function hasBeenSettled(): bool
    {
        return in_array($this->status, [
            PaymentStatus::Succeeded,
            PaymentStatus::RefundPending,
            PaymentStatus::Refunded
        ], true);
    }

    public function providerReferenceId(): string
    {
        return 'payment-' . $this->id;
    }

    public function refundReferenceId(): string
    {
        return 'refund-payment' . $this->id;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
