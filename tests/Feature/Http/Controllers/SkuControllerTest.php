<?php

use App\Models\Product;
use App\Models\Sku;
use App\Models\User;
use App\Sku\SkuStatus;

describe('store', function () {
    it('redirects guests to login', function () {
        $product = Product::factory()->create();

        $this->post(route('products.skus.store', $product), [])
            ->assertRedirect(route('login'));
    });

    it('creates a SKU for the product', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->post(route('products.skus.store', $product), [
                'code' => 'SKU-BLUE-S',
                'name' => 'Blue / Small',
                'price' => 125000,
                'status' => SkuStatus::Active->value,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.edit', $product));

        $this->assertDatabaseHas('skus', [
            'product_id' => $product->id,
            'code' => 'SKU-BLUE-S',
            'name' => 'Blue / Small',
            'price' => 125000,
            'status' => SkuStatus::Active->value,
        ]);
    });

    it('rejects missing required fields', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->post(route('products.skus.store', $product), [])
            ->assertSessionHasErrors(['code', 'name', 'price', 'status']);

        expect(Sku::query()->count())->toBe(0);
    });

    it('rejects a duplicate code and invalid values', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Sku::factory()->for($product)->create(['code' => 'SKU-EXISTS']);

        $this->actingAs($user)
            ->post(route('products.skus.store', $product), [
                'code' => 'SKU-EXISTS',
                'name' => 'Invalid SKU',
                'price' => -1,
                'status' => 'archived',
            ])
            ->assertSessionHasErrors(['code', 'price', 'status']);

        expect(Sku::query()->count())->toBe(1);
    });
});

describe('update', function () {
    it('updates a SKU that belongs to the product', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $sku = Sku::factory()->for($product)->create(['code' => 'SKU-OLD']);

        $this->actingAs($user)
            ->put(route('products.skus.update', [$product, $sku]), [
                'code' => 'SKU-NEW',
                'name' => 'Updated SKU',
                'price' => 250000,
                'status' => SkuStatus::Inactive->value,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.edit', $product));

        expect($sku->refresh())
            ->code->toBe('SKU-NEW')
            ->name->toBe('Updated SKU')
            ->price->toBe(250000)
            ->status->toBe(SkuStatus::Inactive);
    });

    it('does not expose a SKU through another product', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $otherProduct = Product::factory()->create();
        $sku = Sku::factory()->for($otherProduct)->create();

        $this->actingAs($user)
            ->put(route('products.skus.update', [$product, $sku]), [
                'code' => 'SKU-CHANGED',
                'name' => 'Changed SKU',
                'price' => 1,
                'status' => SkuStatus::Active->value,
            ])
            ->assertNotFound();

        expect($sku->refresh()->code)->not->toBe('SKU-CHANGED');
    });
});

describe('destroy', function () {
    it('deletes a SKU that belongs to the product', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $sku = Sku::factory()->for($product)->create();

        $this->actingAs($user)
            ->delete(route('products.skus.destroy', [$product, $sku]))
            ->assertRedirect(route('products.edit', $product));

        $this->assertModelMissing($sku);
    });
});
