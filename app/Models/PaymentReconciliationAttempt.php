<?php

namespace App\Models;

use App\Enums\PaymentReconciliationAttempt\PaymentReconciliationAttemptOutcome;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property int $payment_id
 * @property string $lease_token
 * @property string $started_at
 * @property string|null $finished_at
 * @property string $outcome
 * @property string|null $provider_status
 * @property string|null $error_type
 * @property string|null $error_message
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentReconciliationAttempt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentReconciliationAttempt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentReconciliationAttempt query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentReconciliationAttempt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentReconciliationAttempt whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentReconciliationAttempt whereErrorType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentReconciliationAttempt whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentReconciliationAttempt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentReconciliationAttempt whereLeaseToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentReconciliationAttempt whereOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentReconciliationAttempt wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentReconciliationAttempt whereProviderStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentReconciliationAttempt whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentReconciliationAttempt whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Guarded(['id'])]
class PaymentReconciliationAttempt extends Model
{
    protected function casts(): array
    {
        return [
            'outcome' => PaymentReconciliationAttemptOutcome::class,
        ];
    }
}
