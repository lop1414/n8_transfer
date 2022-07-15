<?php

namespace App\Traits\ProductType;

use App\Common\Enums\ProductTypeEnums;

trait DyMiniProgram
{

    public function getProductType(): string
    {
        return ProductTypeEnums::DY_MINI_PROGRAM;
    }
}
