<?php

use App\Models\Inventory;
use App\Models\Sku;
use App\Models\User;
use App\Models\Warehouse;

describe('store', function () {
    it('creates inventory for a warehouse', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $sku = Sku::factory()->create();

        $this->actingAs($user)
            ->post(route('warehouses.inventories.store', $warehouse), [
                'sku_id' => $sku->id,
                'quantity' => 25,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('warehouses.edit', $warehouse));

        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $warehouse->id,
            'sku_id' => $sku->id,
            'quantity' => 25,
        ]);
    });

    it('rejects the same SKU twice in one warehouse', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $sku = Sku::factory()->create();
        Inventory::factory()->for($warehouse)->for($sku)->create();

        $this->actingAs($user)
            ->post(route('warehouses.inventories.store', $warehouse), [
                'sku_id' => $sku->id,
                'quantity' => 10,
            ])
            ->assertSessionHasErrors('sku_id');

        expect(Inventory::query()->count())->toBe(1);
    });

    it('allows the same SKU in a different warehouse', function () {
        $user = User::factory()->create();
        $firstWarehouse = Warehouse::factory()->create();
        $secondWarehouse = Warehouse::factory()->create();
        $sku = Sku::factory()->create();
        Inventory::factory()->for($firstWarehouse)->for($sku)->create();

        $this->actingAs($user)
            ->post(route('warehouses.inventories.store', $secondWarehouse), [
                'sku_id' => $sku->id,
                'quantity' => 10,
            ])
            ->assertSessionHasNoErrors();

        expect(Inventory::query()->where('sku_id', $sku->id)->count())->toBe(2);
    });

    it('rejects invalid inventory values', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $this->actingAs($user)
            ->post(route('warehouses.inventories.store', $warehouse), [
                'sku_id' => PHP_INT_MAX,
                'quantity' => -1,
            ])
            ->assertSessionHasErrors(['sku_id', 'quantity']);
    });
});

describe('update and destroy', function () {
    it('updates inventory while allowing its current SKU', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $inventory = Inventory::factory()->for($warehouse)->create();

        $this->actingAs($user)
            ->put(route('warehouses.inventories.update', [$warehouse, $inventory]), [
                'sku_id' => $inventory->sku_id,
                'quantity' => 75,
            ])
            ->assertSessionHasNoErrors();

        expect($inventory->refresh()->quantity)->toBe(75);
    });

    it('does not expose inventory through another warehouse', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $otherWarehouse = Warehouse::factory()->create();
        $inventory = Inventory::factory()->for($otherWarehouse)->create();

        $this->actingAs($user)
            ->delete(route('warehouses.inventories.destroy', [$warehouse, $inventory]))
            ->assertNotFound();

        $this->assertModelExists($inventory);
    });

    it('deletes inventory from its warehouse', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $inventory = Inventory::factory()->for($warehouse)->create();

        $this->actingAs($user)
            ->delete(route('warehouses.inventories.destroy', [$warehouse, $inventory]))
            ->assertRedirect(route('warehouses.edit', $warehouse));

        $this->assertModelMissing($inventory);
    });
});
