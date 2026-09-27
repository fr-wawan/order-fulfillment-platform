<?php

namespace App\Models;

use App\Enums\InventoryReservation\InventoryReservationStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $inventory_id
 * @property int $order_item_id
 * @property int $quantity
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property InventoryReservationStatus $status
 * @property-read Inventory $inventory
 * @property-read OrderItem $orderItem
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryReservation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryReservation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryReservation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryReservation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryReservation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryReservation whereInventoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryReservation whereOrderItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryReservation whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryReservation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryReservation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Guarded(['id'])]
class InventoryReservation extends Model
{
    protected function casts(): array
    {
        return [
            'status' => InventoryReservationStatus::class,
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
