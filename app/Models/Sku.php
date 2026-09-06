<?php

namespace App\Models;

use App\Enums\Sku\SkuStatus;
use Database\Factories\SkuFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
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
            'status' => SkuStatus::class,
        ];
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
