<?php

namespace App\Services\UserAction\CompleteOrder;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Bm;
use App\Traits\ProductType\Kyy;
use App\Traits\UserAction\CompleteOrder;


class BmKyyCompleteOrderService extends UserActionAbstract
{
    use Kyy;
    use Bm;
    use CompleteOrder;


    public function get(array $product, string $startTime,string $endTime): array
    {
        $sdk = $this->getSdk($product);
        $data = [];
        $page = 1;
        do{
            $tmp =  $sdk->getPayOrders($startTime, $endTime, $page);
            foreach ($tmp['list'] as $item){
                if($item['orderStatus'] == 1) {
                    $data[] = $this->itemFilter($item);
                }
            }
            $page += 1;

        }while($page <= $tmp['totalPage']);

        return $data;
    }


    public function itemFilter($item): array
    {
        $extend = array_merge(['order_id'=> $item['orderSn']],$this->filterExtendInfo($item));
        return [
            'open_id'       => $item['uuid'],
            'action_time'   => date('Y-m-d H:i:s',$item['payTime']),
            'type'          => $this->getType(),
            'cp_channel_id' => $item['channelid'],
            'request_id'    => '',
            'ip'            => '',
            'extend'        => $extend,
            'data'          => $item,
            'action_id'     => $item['orderSn']
        ];
    }


}
