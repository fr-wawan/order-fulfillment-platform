<?php

namespace App\Exceptions\Order;

use Exception;

class OrderNotPayableException extends Exception
{
    public function __construct()
    {
        return parent::__construct('Order is not payable.', 409);
    }
}
