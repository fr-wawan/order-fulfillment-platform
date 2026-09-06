<?php

use App\Enums\Warehouse\WarehouseStatus;
use App\Models\Inventory;
use App\Models\Sku;
use App\Models\User;
use App\Models\Warehouse;
use Inertia\Testing\AssertableInertia as Assert;

describe('index', function () {
    it('redirects guests to login', function () {
        $this->get(route('warehouses.index'))->assertRedirect(route('login'));
    });

    it('renders warehouses for authenticated users', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $this->actingAs($user)
            ->get(route('warehouses.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('warehouses/Index')
                ->has('warehouses.data', 1)
                ->where('warehouses.data.0.id', $warehouse->id));
    });
});

describe('forms', function () {
    it('renders the create form with disabled inventory data', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('warehouses.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('warehouses/Form')
                ->where('warehouse', null)
                ->where('inventories', null)
                ->where('assignedSkuIds', []));
    });

    it('renders the edit form with inventory and SKU options', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $sku = Sku::factory()->create();
        $inventory = Inventory::factory()->for($warehouse)->for($sku)->create();

        $this->actingAs($user)
            ->get(route('warehouses.edit', $warehouse))
            ->assertInertia(fn (Assert $page) => $page
                ->component('warehouses/Form')
                ->where('warehouse.id', $warehouse->id)
                ->has('inventories.data', 1)
                ->where('inventories.data.0.id', $inventory->id)
                ->where('skuOptions.0.id', $sku->id)
                ->where('assignedSkuIds.0', $sku->id));
    });
});

describe('store', function () {
    it('creates a warehouse', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('warehouses.store'), [
                'code' => 'WH-MAIN',
                'name' => 'Main Warehouse',
                'status' => WarehouseStatus::Active->value,
            ])
            ->assertSessionHasNoErrors();

        $warehouse = Warehouse::query()->where('code', 'WH-MAIN')->firstOrFail();

        $this->assertDatabaseHas('warehouses', [
            'code' => 'WH-MAIN',
            'name' => 'Main Warehouse',
        ]);
        expect($warehouse->status)->toBe(WarehouseStatus::Active);
    });

    it('rejects missing fields and duplicate codes', function () {
        $user = User::factory()->create();
        Warehouse::factory()->create(['code' => 'WH-EXISTS']);

        $this->actingAs($user)
            ->post(route('warehouses.store'), [
                'code' => 'WH-EXISTS',
                'name' => '',
                'status' => 'archived',
            ])
            ->assertSessionHasErrors(['code', 'name', 'status']);

        expect(Warehouse::query()->count())->toBe(1);
    });
});

describe('update and destroy', function () {
    it('updates a warehouse', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create(['code' => 'WH-OLD']);

        $this->actingAs($user)
            ->put(route('warehouses.update', $warehouse), [
                'code' => 'WH-NEW',
                'name' => 'Updated Warehouse',
                'status' => WarehouseStatus::Inactive->value,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('warehouses.index'));

        expect($warehouse->refresh())
            ->code->toBe('WH-NEW')
            ->name->toBe('Updated Warehouse')
            ->status->toBe(WarehouseStatus::Inactive);
    });

    it('deletes a warehouse', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $this->actingAs($user)
            ->delete(route('warehouses.destroy', $warehouse))
            ->assertRedirect(route('warehouses.index'));

        $this->assertModelMissing($warehouse);
    });
});
