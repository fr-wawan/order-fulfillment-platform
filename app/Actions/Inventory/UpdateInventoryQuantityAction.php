<?php

namespace App\Actions\Inventory;

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateInventoryQuantityAction
{
    public function handle(Inventory $inventory, int $quantity): Inventory
    {
        return DB::transaction(function () use ($inventory, $quantity) {
            $inventory = Inventory::query()
                ->lockForUpdate()
                ->findOrFail($inventory->id);

            if ($inventory->reserved_quantity > $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'New quantity cannot be less than reserved quantity',
                ]);
            }

            $inventory->update([
                'quantity' => $quantity,
            ]);

            return $inventory;
        });
    }
}
