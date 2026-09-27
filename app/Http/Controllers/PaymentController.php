<?php

namespace App\Http\Controllers;

use App\Actions\Payment\InitiatePaymentAction;
use App\Models\Order;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function store(Order $order, InitiatePaymentAction $initiatePaymentAction): Response
    {
        $payment = $initiatePaymentAction->handle($order);

        return Inertia::location($payment->checkout_url);
    }
}
