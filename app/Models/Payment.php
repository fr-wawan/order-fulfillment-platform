<?php

namespace App\Models;

use App\Enums\Payment\PaymentSessionStatus;
use App\Enums\Payment\PaymentStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $order_id
 * @property string|null $provider_payment_id
 * @property int $amount
 * @property CarbonImmutable|null $paid_at
 * @property string|null $provider_session_id
 * @property string|null $checkout_url
 * @property PaymentStatus $status
 * @property PaymentSessionStatus $session_status
 * @property string|null $provider_payment_request_id
 * @property string|null $provider_refund_id
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property string|null $next_reconciliation_at
 * @property string|null $reconciliation_lease_until
 * @property string|null $reconciliation_lease_token
 * @property string|null $last_reconciled_at
 * @property int $consecutive_reconciliation_failures
 * @property string|null $reconciliation_stopped_at
 * @property-read \App\Models\Order $order
 * @method static Builder<static>|Payment claimableForReconciliation()
 * @method static Builder<static>|Payment newModelQuery()
 * @method static Builder<static>|Payment newQuery()
 * @method static Builder<static>|Payment query()
 * @method static Builder<static>|Payment whereAmount($value)
 * @method static Builder<static>|Payment whereCheckoutUrl($value)
 * @method static Builder<static>|Payment whereConsecutiveReconciliationFailures($value)
 * @method static Builder<static>|Payment whereCreatedAt($value)
 * @method static Builder<static>|Payment whereId($value)
 * @method static Builder<static>|Payment whereLastReconciledAt($value)
 * @method static Builder<static>|Payment whereNextReconciliationAt($value)
 * @method static Builder<static>|Payment whereOrderId($value)
 * @method static Builder<static>|Payment wherePaidAt($value)
 * @method static Builder<static>|Payment whereProviderPaymentId($value)
 * @method static Builder<static>|Payment whereProviderPaymentRequestId($value)
 * @method static Builder<static>|Payment whereProviderRefundId($value)
 * @method static Builder<static>|Payment whereProviderSessionId($value)
 * @method static Builder<static>|Payment whereReconciliationLeaseToken($value)
 * @method static Builder<static>|Payment whereReconciliationLeaseUntil($value)
 * @method static Builder<static>|Payment whereReconciliationStoppedAt($value)
 * @method static Builder<static>|Payment whereSessionStatus($value)
 * @method static Builder<static>|Payment whereStatus($value)
 * @method static Builder<static>|Payment whereUpdatedAt($value)
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
            'paid_at' => 'datetime',
        ];
    }

    #[Scope]
    protected function claimableForReconciliation(Builder $query): void
    {
        $now = now();

        $query->where('status', PaymentStatus::Pending)
            ->where('session_status', PaymentSessionStatus::Ready)
            ->whereNotNull('provider_session_id')
            ->whereNull('reconciliation_stopped_at')
            ->whereNotNull('next_reconciliation_at')
            ->where('next_reconciliation_at', '<=', $now)
            ->where(function (Builder $query) use ($now) {
                $query
                    ->whereNull('reconciliation_lease_until')
                    ->orWhere('reconciliation_lease_until', '<=', $now);
            });
    }

    public function providerReferenceId(): string
    {
        return 'payment-' . $this->id;
    }

    public function refundReferenceId(): string
    {
        return 'refund-payment' . $this->id;
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::Pending;
    }

    public function hasFinalRefundStatus(): bool
    {
        return in_array($this->status, [
            PaymentStatus::Refunded,
            PaymentStatus::RefundFailed,
        ], true);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function reconciliationAttempts(): HasMany
    {
        return $this->hasMany(PaymentReconciliationAttempt::class);
    }
}
