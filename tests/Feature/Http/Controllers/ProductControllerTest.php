<?php

use App\Models\Product;
use App\Models\Sku;
use App\Models\User;
use App\Product\ProductStatus;
use Inertia\Testing\AssertableInertia as Assert;

describe('index', function () {
    it('redirects guests to login', function () {
        $this->get(route('products.index'))
            ->assertRedirect(route('login'));
    });

    it('renders the paginated product list for authenticated users', function () {
        $user = User::factory()->create();
        $olderProduct = Product::factory()->create(['name' => 'Older product']);
        $newerProduct = Product::factory()->create(['name' => 'Newer product']);

        $this->actingAs($user)
            ->get(route('products.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('products/Index')
                ->has('products.data', 2)
                ->where('products.data.0.id', $newerProduct->id)
                ->where('products.data.1.id', $olderProduct->id));
    });
});

describe('create and edit forms', function () {
    it('renders the create form', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('products.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('products/Form')
                ->where('product', null)
                ->where('skus', null));
    });

    it('renders the edit form with the selected product', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $sku = Sku::factory()->for($product)->create();

        $this->actingAs($user)
            ->get(route('products.edit', $product))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('products/Form')
                ->where('product.id', $product->id)
                ->where('product.name', $product->name)
                ->has('skus.data', 1)
                ->where('skus.data.0.id', $sku->id));
    });
});

describe('store', function () {
    it('creates a valid product', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('products.store'), [
                'name' => 'Desk lamp',
                'description' => 'A compact reading lamp.',
                'status' => ProductStatus::Active->value,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Desk lamp',
            'description' => 'A compact reading lamp.',
            'status' => ProductStatus::Active->value,
        ]);
    });

    it('rejects missing required fields', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('products.store'), [])
            ->assertSessionHasErrors(['name', 'status']);

        expect(Product::query()->count())->toBe(0);
    });

    it('rejects an invalid status', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('products.store'), [
                'name' => 'Desk lamp',
                'status' => 'archived',
            ])
            ->assertSessionHasErrors('status');

        expect(Product::query()->count())->toBe(0);
    });
});

describe('update', function () {
    it('updates a valid product', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Old name',
            'status' => ProductStatus::Inactive,
        ]);

        $this->actingAs($user)
            ->put(route('products.update', $product), [
                'name' => 'New name',
                'description' => null,
                'status' => ProductStatus::Active->value,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.index'));

        expect($product->refresh())
            ->name->toBe('New name')
            ->description->toBeNull()
            ->status->toBe(ProductStatus::Active);
    });
});

describe('destroy', function () {
    it('deletes the product', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertModelMissing($product);
    });
});
