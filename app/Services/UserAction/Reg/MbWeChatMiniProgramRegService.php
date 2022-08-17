<?php

namespace App\Services\UserAction\Reg;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Mb;
use App\Traits\ProductType\WeChatMiniProgram;
use App\Traits\UserAction\Reg;


class MbWeChatMiniProgramRegService extends UserActionAbstract
{
    use WeChatMiniProgram;
    use Mb;
    use Reg;


    public function get(array $product, string $startTime,string $endTime): array
    {
        return (new MbDyMiniProgramRegService())->get($product,$startTime,$endTime);
    }





}
