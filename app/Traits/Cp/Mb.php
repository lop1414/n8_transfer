<?php

namespace App\Traits\Cp;


use App\Common\Enums\CpTypeEnums;
use App\Common\Sdks\Mb\MbSdk;

trait Mb
{

    public function getCpType(): string
    {
        return CpTypeEnums::MB;
    }


    protected function getSdk(array $product): MbSdk
    {
        return  new MbSdk($product['cp_account']['account'],$product['cp_product_alias'],$product['cp_account']['cp_secret']);
    }



}
