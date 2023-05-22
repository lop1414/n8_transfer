<?php

namespace App\Services\UserAction\Order;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Yg;
use App\Traits\ProductType\WeChatMiniProgram;
use App\Traits\UserAction\Order;


class YgWeChatMiniProgramOrderService extends UserActionAbstract
{
    use WeChatMiniProgram;
    use Yg;
    use Order;


    public function get(array $product, string $startTime,string $endTime): array
    {
        $sdk = $this->getSdk($product);

        $sdk->getOrders($startTime,$endTime);

        return [];
    }


}
