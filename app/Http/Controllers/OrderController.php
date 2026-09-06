<?php

namespace App\Http\Controllers;

use App\Actions\Order\CreateOrderAction;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Models\Order;
use App\Models\Sku;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('orders/Index', [
            'orders' => Order::query()
                ->withCount('items')
                ->latest('id')
                ->paginate(10),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('orders/Create', [
            'skuOptions' => Sku::query()
                ->with('product:id,name')
                ->orderBy('code')
                ->get(['id', 'product_id', 'code', 'name', 'price', 'status']),
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load([
            'items' => fn ($query) => $query
                ->with('sku.product')
                ->oldest('id'),
        ]);

        return Inertia::render('orders/Show', [
            'order' => $order,
        ]);
    }

    public function store(StoreOrderRequest $request, CreateOrderAction $action): RedirectResponse
    {
        $action->handle($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Order created successfully.']);

        return to_route('orders.index');
    }
}
