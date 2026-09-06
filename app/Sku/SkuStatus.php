<?php

namespace App\Sku;

enum SkuStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
