<?php

namespace App\Http\Controllers;

use App\Actions\Inventory\UpdateInventoryQuantityAction;
use App\Http\Requests\Inventory\StoreInventoryRequest;
use App\Http\Requests\Inventory\UpdateInventoryRequest;
use App\Models\Inventory;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function store(StoreInventoryRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->inventories()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Inventory created successfully.']);

        return to_route('warehouses.edit', $warehouse);
    }

    public function update(
        UpdateInventoryRequest $request,
        Warehouse $warehouse,
        Inventory $inventory,
        UpdateInventoryQuantityAction $action,
    ): RedirectResponse {
        $action->handle($inventory, $request->integer('quantity'));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Inventory updated successfully.']);

        return to_route('warehouses.edit', $warehouse);
    }
}
