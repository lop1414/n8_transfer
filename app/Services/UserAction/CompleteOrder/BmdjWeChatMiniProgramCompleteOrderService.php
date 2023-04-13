<?php

namespace App\Services\UserAction\CompleteOrder;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Bmdj;
use App\Traits\ProductType\WeChatMiniProgram;
use App\Traits\UserAction\CompleteOrder;


class BmdjWeChatMiniProgramCompleteOrderService extends UserActionAbstract
{
    use WeChatMiniProgram;
    use Bmdj;
    use CompleteOrder;


    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);

        $data = [];
        $page = 1;
        do{
            $list =  $sdk->getOrders($startTime,$endTime,$page);

            foreach ($list['list'] as $item){
                if($item['status'] == 1) {
                    $data[] = $this->itemFilter($item);
                }
            }

            $page += 1;
        }while($list['page'] < $list['totalPage']);

        return $data;
    }


    public function itemFilter($item): array
    {
        return [
            'open_id'       => $item['uuid'],
            'action_time'   => date('Y-m-d H:i:s',$item['payTime']),
            'type'          => $this->getType(),
            'cp_channel_id' => $item['channelid'],
            'request_id'    => '',
            'ip'            => '',
            'data'          => $item,
            'action_id'     => $item['orderSn'],
            'extend'        => array_merge([
                'order_id'      => $item['orderSn']
            ],$this->filterExtendInfo($item)),
        ];
    }


}
