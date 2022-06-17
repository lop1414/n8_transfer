<?php

namespace App\Traits\Cp;


use App\Common\Enums\CpTypeEnums;
use App\Common\Sdks\Zy\ZySdk;

trait Zy
{

    public function getCpType(): string
    {
        return CpTypeEnums::ZY;
    }


    protected function getSdk(array $product): ZySdk
    {
        return new ZySdk($product['cp_product_alias'],$product['cp_secret']);
    }


}
