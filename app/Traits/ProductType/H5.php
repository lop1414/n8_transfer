<?php

namespace App\Traits\ProductType;

use App\Common\Enums\ProductTypeEnums;

trait H5
{

    public function getProductType(): string
    {
        return ProductTypeEnums::H5;
    }
}
