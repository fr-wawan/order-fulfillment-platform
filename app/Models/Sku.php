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
