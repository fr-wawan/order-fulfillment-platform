<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Override;

class OrderNotPayableException extends Exception
{
    public function __construct()
    {
        return parent::__construct('Order is not payable.', 409);
    }
}
