<?php

namespace App\Traits\Cp;


use App\Common\Enums\CpTypeEnums;
use App\Common\Sdks\TwH5\TwH5Sdk;

trait TwH5
{

    public function getCpType(): string
    {
        return CpTypeEnums::TW;
    }


    protected function getSdk(array $product): TwH5Sdk
    {
        return new TwH5Sdk($product['cp_product_alias'],$product['cp_secret']);
    }



}
