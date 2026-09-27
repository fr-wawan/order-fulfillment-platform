<?php

namespace App\Models;

use App\Enums\Sku\SkuStatus;
use Database\Factories\SkuFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $product_id
 * @property string $code
 * @property string $name
 * @property int $price
 * @property SkuStatus $status
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory> $inventories
 * @property-read int|null $inventories_count
 * @property-read \App\Models\Product $product
 * @method static \Database\Factories\SkuFactory factory($count = null, $state = [])
 * @method static Builder<static>|Sku newModelQuery()
 * @method static Builder<static>|Sku newQuery()
 * @method static Builder<static>|Sku query()
 * @method static Builder<static>|Sku whereCode($value)
 * @method static Builder<static>|Sku whereCreatedAt($value)
 * @method static Builder<static>|Sku whereId($value)
 * @method static Builder<static>|Sku whereName($value)
 * @method static Builder<static>|Sku wherePrice($value)
 * @method static Builder<static>|Sku whereProductId($value)
 * @method static Builder<static>|Sku whereStatus($value)
 * @method static Builder<static>|Sku whereUpdatedAt($value)
 * @method static Builder<static>|Sku withAvailableQuantity()
 * @mixin \Eloquent
 */
#[Guarded(['id'])]
class Sku extends Model
{
    /** @use HasFactory<SkuFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'available_quantity' => 'integer',
            'status' => SkuStatus::class,
        ];
    }

    /** @param Builder<Sku> $query */
    #[Scope]
    protected function withAvailableQuantity(Builder $query): void
    {
        $inventory = new Inventory;

        $query->addSelect([
            'available_quantity' => $inventory->newQuery()
                ->selectRaw('COALESCE(MAX(quantity - reserved_quantity), 0)')
                ->whereColumn(
                    $inventory->qualifyColumn('sku_id'),
                    $query->getModel()->qualifyColumn('id'),
                ),
        ]);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }
}
