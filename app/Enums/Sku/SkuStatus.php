<?php

namespace App\Enums\Sku;

enum SkuStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
