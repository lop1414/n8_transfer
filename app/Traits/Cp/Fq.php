<?php

namespace App\Traits\Cp;


use App\Common\Enums\CpTypeEnums;
use App\Common\Sdks\Fq\FqSdk;

trait Fq
{

    public function getCpType(): string
    {
        return CpTypeEnums::FQ;
    }


    protected function getSdk(array $product): FqSdk
    {
        return new FqSdk($product['cp_product_alias'],$product['cp_secret']);
    }



}
