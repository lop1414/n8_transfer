<?php

namespace App\Traits\ProductType;

use App\Common\Enums\ProductTypeEnums;

trait Kyy
{

    public function getProductType(): string
    {
        return ProductTypeEnums::KYY;
    }
}
