<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Override;

class PaymentInitiationInProgressException extends Exception
{
    public function __construct()
    {
        return parent::__construct('Payment initiation is already in progress');
    }
}
