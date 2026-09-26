<?php

use App\Actions\Inventory\UpdateInventoryQuantityAction;
use App\Models\Inventory;
use Illuminate\Validation\ValidationException;

it('updates quantity without changing the reserved quantity', function () {
    $inventory = Inventory::factory()->create([
        'quantity' => 10,
        'reserved_quantity' => 3,
    ]);

    $updatedInventory = app(UpdateInventoryQuantityAction::class)->handle($inventory, 7);

    expect($updatedInventory)
        ->id->toBe($inventory->id)
        ->quantity->toBe(7)
        ->reserved_quantity->toBe(3);
    expect($inventory->refresh())
        ->quantity->toBe(7)
        ->reserved_quantity->toBe(3);
});

it('rejects a quantity below the reserved quantity', function () {
    $inventory = Inventory::factory()->create([
        'quantity' => 10,
        'reserved_quantity' => 3,
    ]);

    expect(fn () => app(UpdateInventoryQuantityAction::class)->handle($inventory, 2))
        ->toThrow(ValidationException::class);

    expect($inventory->refresh())
        ->quantity->toBe(10)
        ->reserved_quantity->toBe(3);
});
