<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sku\StoreSkuRequest;
use App\Http\Requests\Sku\UpdateSkuRequest;
use App\Models\Product;
use App\Models\Sku;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class SkuController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSkuRequest $request, Product $product): RedirectResponse
    {
        $product->skus()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Sku created successfully.']);

        return to_route('products.edit', $product->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSkuRequest $request, Product $product, Sku $sku): RedirectResponse
    {
        $sku->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Sku updated successfully.']);

        return to_route('products.edit', $product->id);
    }
}
