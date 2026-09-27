<?php

namespace App\Models;

use App\Enums\Order\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $order_number
 * @property OrderStatus $status
 * @property int $total_amount
 * @property \Carbon\CarbonImmutable $expires_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Payment|null $payment
 * @method static Builder<static>|Order dueForExpiration()
 * @method static Builder<static>|Order newModelQuery()
 * @method static Builder<static>|Order newQuery()
 * @method static Builder<static>|Order query()
 * @method static Builder<static>|Order whereCreatedAt($value)
 * @method static Builder<static>|Order whereExpiresAt($value)
 * @method static Builder<static>|Order whereId($value)
 * @method static Builder<static>|Order whereOrderNumber($value)
 * @method static Builder<static>|Order whereStatus($value)
 * @method static Builder<static>|Order whereTotalAmount($value)
 * @method static Builder<static>|Order whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Guarded(['id'])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'status' => OrderStatus::class,
        ];
    }

    #[Scope]
    protected function dueForExpiration(Builder $query): void
    {
        $query->where('status', OrderStatus::Pending)
            ->where('expires_at', '<=', now());
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
