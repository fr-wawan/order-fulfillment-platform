<?php

namespace App\Exceptions\Payment;

use Exception;

class UnexpectedPaymentStatusException extends Exception
{
    public function __construct(string $status)
    {
        parent::__construct("Unexpected payment status: {$status}");
    }
}
