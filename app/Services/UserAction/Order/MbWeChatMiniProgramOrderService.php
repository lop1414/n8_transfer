<?php

namespace App\Services\UserAction\Order;

use App\Services\UserAction\CompleteOrder\MbDyMiniProgramCompleteOrderService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Mb;
use App\Traits\ProductType\WeChatMiniProgram;
use App\Traits\UserAction\Order;


class MbWeChatMiniProgramOrderService extends UserActionAbstract
{
    use WeChatMiniProgram;
    use Mb;
    use Order;

    protected $mbDyMiniProgramCompleteOrderService;

    public function __construct()
    {
        $this->mbDyMiniProgramCompleteOrderService = new MbDyMiniProgramCompleteOrderService();
    }


    public function get(array $product, string $startTime,string $endTime): array
    {
        return (new MbDyMiniProgramOrderService())->get($product,$startTime,$endTime);
    }


}
