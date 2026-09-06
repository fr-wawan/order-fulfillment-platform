<?php

namespace App\Product;

enum ProductStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
