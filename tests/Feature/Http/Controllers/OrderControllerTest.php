<?php

use App\Actions\Order\CreateOrderAction;
use App\Enums\Sku\SkuStatus;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Sku;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

describe('index', function () {
    it('redirects guests to login', function () {
        $this->get(route('orders.index'))->assertRedirect(route('login'));
    });

    it('renders the order list for authenticated users', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('orders.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('orders/Index')
                ->has('orders.data', 0));
    });
});

describe('create', function () {
    it('renders active and inactive SKU options', function () {
        $user = User::factory()->create();
        $activeSku = Sku::factory()->create([
            'code' => 'SKU-ACTIVE',
            'status' => SkuStatus::Active,
        ]);
        $inactiveSku = Sku::factory()->create([
            'code' => 'SKU-INACTIVE',
            'status' => SkuStatus::Inactive,
        ]);
        Inventory::factory()->for($activeSku)->create([
            'quantity' => 10,
            'reserved_quantity' => 4,
        ]);
        Inventory::factory()->for($activeSku)->create([
            'quantity' => 8,
            'reserved_quantity' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('orders.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('orders/Create')
                ->has('skuOptions', 2)
                ->where('skuOptions.0.id', $activeSku->id)
                ->where('skuOptions.0.available_quantity', 7)
                ->where('skuOptions.1.id', $inactiveSku->id)
                ->where('skuOptions.1.available_quantity', 0));
    });
});

describe('show', function () {
    it('renders order details with item price snapshots', function () {
        $user = User::factory()->create();
        $sku = Sku::factory()->create(['price' => 2_500]);
        Inventory::factory()->for($sku)->create(['quantity' => 2]);
        $order = app(CreateOrderAction::class)->handle([
            'items' => [['sku_id' => $sku->id, 'quantity' => 2]],
        ]);

        $this->actingAs($user)
            ->get(route('orders.show', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->component('orders/Show')
                ->where('order.id', $order->id)
                ->where('order.total_amount', 5_000)
                ->has('order.items', 1)
                ->where('order.items.0.sku.id', $sku->id)
                ->where('order.items.0.unit_price', 2_500));
    });
});

describe('store', function () {
    it('returns the stock error when inventory is insufficient', function () {
        $user = User::factory()->create();
        $sku = Sku::factory()->create([
            'code' => 'SKU-LIMITED',
            'name' => 'Limited SKU',
        ]);
        Inventory::factory()->for($sku)->create([
            'quantity' => 5,
            'reserved_quantity' => 4,
        ]);

        $this->actingAs($user)
            ->post(route('orders.store'), [
                'items' => [['sku_id' => $sku->id, 'quantity' => 2]],
            ])
            ->assertSessionHasErrors([
                'items' => 'SKU SKU-LIMITED (Limited SKU) does not have enough available stock for 2 units.',
            ]);

        expect(Order::query()->count())->toBe(0);
    });

    it('creates an order containing an inactive SKU', function () {
        $user = User::factory()->create();
        $inactiveSku = Sku::factory()->create([
            'price' => 1_250,
            'status' => SkuStatus::Inactive,
        ]);
        Inventory::factory()->for($inactiveSku)->create(['quantity' => 3]);

        $this->actingAs($user)
            ->post(route('orders.store'), [
                'items' => [
                    ['sku_id' => $inactiveSku->id, 'quantity' => 2],
                    ['sku_id' => $inactiveSku->id, 'quantity' => 1],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('orders.index'));

        $order = Order::query()->firstOrFail();

        expect($order->total_amount)->toBe(3_750);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'sku_id' => $inactiveSku->id,
            'quantity' => 3,
            'unit_price' => 1_250,
        ]);
    });

    it('rolls back the entire order when an item cannot be created', function () {
        $sku = Sku::factory()->create(['price' => 1_250]);
        $inventory = Inventory::factory()->for($sku)->create(['quantity' => 2]);
        OrderItem::creating(fn () => throw new RuntimeException('Order item failed.'));

        expect(fn () => app(CreateOrderAction::class)->handle([
            'items' => [['sku_id' => $sku->id, 'quantity' => 2]],
        ]))->toThrow(RuntimeException::class, 'Order item failed.');

        expect(Order::query()->count())->toBe(0);
        expect(OrderItem::query()->count())->toBe(0);
        expect($inventory->refresh()->reserved_quantity)->toBe(0);
    });

    it('keeps the original item price after the SKU price changes', function () {
        $sku = Sku::factory()->create(['price' => 1_250]);
        Inventory::factory()->for($sku)->create(['quantity' => 2]);
        $order = app(CreateOrderAction::class)->handle([
            'items' => [['sku_id' => $sku->id, 'quantity' => 2]],
        ]);

        $sku->update(['price' => 9_999]);

        $orderItem = $order->items()->sole();

        expect($orderItem->unit_price)->toBe(1_250);
        expect($order->refresh()->total_amount)->toBe(2_500);
    });
});
