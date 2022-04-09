<?php

namespace App\Traits\ProductType;

use App\Common\Enums\ProductTypeEnums;

trait App
{

    public function getProductType(): string
    {
        return ProductTypeEnums::APP;
    }
}
