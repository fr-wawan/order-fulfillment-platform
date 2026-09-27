<?php

namespace App\Http\Controllers;

use App\Actions\Payment\ProcessXenditPaymentWebhookAction;
use App\Actions\Payment\ProcessXenditRefundWebhookAction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class XenditPaymentWebhookController extends Controller
{
    public function __invoke(Request $request, ProcessXenditPaymentWebhookAction $paymentAction, ProcessXenditRefundWebhookAction $refundAction): Response
    {
        if (! hash_equals(
            (string) config('services.xendit.webhook_token'),
            (string) $request->header('x-callback-token')
        )) {
            abort(401);
        }

        match ($request->input('event')) {
            'payment_session.completed' => $paymentAction->handle(
                $request->input('data')
            ),
            'refund.succeeded' => $refundAction->handle(
                $request->input('data')
            ),
            default => null
        };

        return response()->noContent();
    }
}
