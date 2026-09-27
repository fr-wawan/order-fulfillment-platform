<?php

namespace App\Models;

use App\Enums\Payment\PaymentSessionStatus;
use App\Enums\Payment\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Override;

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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
