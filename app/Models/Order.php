<?php

namespace App\Models;

use App\Enums\Order\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
