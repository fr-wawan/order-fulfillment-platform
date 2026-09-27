<?php

namespace App\Exceptions\Payment;

use Exception;

class PaymentMismatchException extends Exception
{
    public function __construct(string $field)
    {
        parent::__construct("Payment {$field} does not match.");
    }
}
