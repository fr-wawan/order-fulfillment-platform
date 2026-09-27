<?php

namespace App\Models;

use Database\Factories\InventoryFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $warehouse_id
 * @property int $sku_id
 * @property int $quantity
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property int $reserved_quantity
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InventoryReservation> $reservations
 * @property-read int|null $reservations_count
 * @property-read \App\Models\Sku $sku
 * @property-read \App\Models\Warehouse $warehouse
 * @method static \Database\Factories\InventoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereReservedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereSkuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereWarehouseId($value)
 * @mixin \Eloquent
 */
#[Guarded(['id'])]
class Inventory extends Model
{
    /** @use HasFactory<InventoryFactory> */
    use HasFactory;

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(Sku::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(InventoryReservation::class);
    }
}
