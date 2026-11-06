<?php

namespace App\Enums\PaymentReconciliationAttempt;

enum PaymentReconciliationAttemptOutcome: string
{
    case Started = 'started';
    case Settled = 'settled';
    case ProviderPending = 'provider_pending';
    case ProviderTerminal = 'provider_terminal';
    case TransientFailure = 'transient_failure';
    case ValidationFailure = 'validation_failure';
    case LeaseLost = 'lease_lost';
    case SupersededByWebhook = 'superseded_by_webhook';
}
