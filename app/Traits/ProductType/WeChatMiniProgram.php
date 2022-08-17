<?php

namespace App\Traits\ProductType;

use App\Common\Enums\ProductTypeEnums;

trait WeChatMiniProgram
{

    public function getProductType(): string
    {
        return ProductTypeEnums::WECHAT_MINI_PROGRAM;
    }
}
