<?php

namespace App\Http\Controllers;

use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Models\Sku;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('warehouses/Index', [
            'warehouses' => Warehouse::query()
                ->latest('id')
                ->paginate(10),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('warehouses/Form', [
            'warehouse' => null,
            'inventories' => null,
            'skuOptions' => [],
            'assignedSkuIds' => [],
        ]);
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $warehouse = Warehouse::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Warehouse created successfully.']);

        return to_route('warehouses.edit', $warehouse);
    }

    public function edit(Warehouse $warehouse): Response
    {
        return Inertia::render('warehouses/Form', [
            'warehouse' => $warehouse,
            'inventories' => $warehouse->inventories()
                ->with('sku.product:id,name')
                ->latest('id')
                ->paginate(10),
            'skuOptions' => Sku::query()
                ->with('product:id,name')
                ->orderBy('code')
                ->get(['id', 'product_id', 'code', 'name', 'status']),
            'assignedSkuIds' => $warehouse->inventories()->pluck('sku_id'),
        ]);
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Warehouse updated successfully.']);

        return to_route('warehouses.index');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Warehouse deleted successfully.']);

        return to_route('warehouses.index');
    }
}
