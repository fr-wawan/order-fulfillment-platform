<?php

namespace App\Http\Controllers;

use App\Actions\Order\CancelOrderAction;
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
                ->select(['id', 'product_id', 'code', 'name', 'price', 'status'])
                ->with('product:id,name')
                ->withAvailableQuantity()
                ->orderBy('code')
                ->get(),
        ]);
    }

    public function store(StoreOrderRequest $request, CreateOrderAction $action): RedirectResponse
    {
        $order = $action->handle($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Order created successfully.']);

        return to_route('orders.show', $order);
    }

    public function show(Order $order): Response
    {
        $order->load([
            'items' => fn ($query) => $query
                ->with('sku.product')
                ->oldest('id'),
            'payment:id,order_id,status',
        ]);

        return Inertia::render('orders/Show', [
            'order' => $order,
        ]);
    }

    public function cancel(Order $order, CancelOrderAction $action): RedirectResponse
    {
        $action->handle($order);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Order cancelled successfully.']);

        return back();
    }
}
