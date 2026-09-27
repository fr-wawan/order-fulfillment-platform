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
        $webhookToken = config('services.xendit.webhook_token');
        $callbackToken = $request->header('x-callback-token');

        if (
            empty($webhookToken) ||
            empty($callbackToken) ||
            ! hash_equals($webhookToken, $callbackToken)
        ) {
            abort(401);
        }

        match ($request->input('event')) {
            'payment_session.completed' => $paymentAction->handle(
                $request->input('data')
            ),
            'refund.succeeded', 'refund.failed' => $refundAction->handle(
                $request->input('data')
            ),
            default => null
        };

        return response()->noContent();
    }
}
