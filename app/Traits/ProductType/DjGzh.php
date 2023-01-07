<?php

namespace App\Traits\ProductType;

use App\Common\Enums\ProductTypeEnums;

trait DjGzh
{

    public function getProductType(): string
    {
        return ProductTypeEnums::DJ_GZH;
    }
}
