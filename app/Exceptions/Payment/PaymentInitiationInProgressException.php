<?php

namespace App\Exceptions\Payment;

use Exception;

class PaymentInitiationInProgressException extends Exception
{
    public function __construct()
    {
        return parent::__construct('Payment initiation is already in progress');
    }
}
