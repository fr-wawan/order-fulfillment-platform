<?php

namespace App\Models;

use App\Enums\InventoryReservation\InventoryReservationStatus;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

#[Guarded(['id'])]
class InventoryReservation extends Model
{
    protected function casts(): array
    {
        return [
            'status' => InventoryReservationStatus::class
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
