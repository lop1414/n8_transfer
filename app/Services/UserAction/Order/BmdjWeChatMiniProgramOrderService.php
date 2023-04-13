<?php

namespace App\Services\UserAction\Order;

use App\Services\UserAction\CompleteOrder\BmdjWeChatMiniProgramCompleteOrderService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Bmdj;
use App\Traits\ProductType\WeChatMiniProgram;
use App\Traits\UserAction\Order;


class BmdjWeChatMiniProgramOrderService extends UserActionAbstract
{
    use WeChatMiniProgram;
    use Bmdj;
    use Order;

    protected $bmdjWeChatMiniProgramCompleteOrderService;

    public function __construct()
    {
        $this->bmdjWeChatMiniProgramCompleteOrderService = new BmdjWeChatMiniProgramCompleteOrderService();
    }


    public function get(array $product, string $startTime,string $endTime): array
    {
        $sdk = $this->getSdk($product);

        $page = 1;
        $data = [];
        do{
            $list =  $sdk->getOrders($startTime,$endTime,$page);
            foreach ($list['list'] as $item){

                $data[] = [
                    'open_id'       => $item['uuid'],
                    'action_time'   => $item['createTime'],
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['channelid'],
                    'request_id'    => '',
                    'ip'            => '',
                    'data'          => $item,
                    'action_id'     => $item['orderSn'],
                    'extend'        => array_merge([
                        'amount'        => $item['orderPrice'],
                        'type'          => $this->getOrderType($item['orderType']),
                        'order_id'      => $item['orderSn']
                    ],$this->filterExtendInfo($item)),
                ];

                if($item['orderStatus'] == 1){
                    $data[] = $this->bmdjWeChatMiniProgramCompleteOrderService->itemFilter($item);
                }
            }
            $page += 1;
        }while($list['page'] < $list['totalPage']);

        return $data;
    }


}
