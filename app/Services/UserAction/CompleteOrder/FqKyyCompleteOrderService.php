<?php

namespace App\Services\UserAction\CompleteOrder;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Fq;
use App\Traits\ProductType\Kyy;
use App\Traits\UserAction\CompleteOrder;


class FqKyyCompleteOrderService extends UserActionAbstract
{
    use Kyy;
    use Fq;
    use CompleteOrder;


    public function get(array $product, string $startTime,string $endTime): array
    {
        $sdk = $this->getSdk($product);
        $offset = 0;
        $data = [];
        do{
            $list =  $sdk->getOrders($startTime,$endTime,$offset);
            foreach ($list['result'] as $item){
                if($item['status'] == 0) {
                    $data[] = $this->itemFilter($item);
                }
            }
            $offset = $list['next_offset'];

        }while($list['has_more']);
        return $data;
    }


    public function itemFilter($item): array
    {
        $extend = array_merge(['order_id'=> $item['trade_no']],$this->filterExtendInfo($item));
        return [
            'open_id'       => $item['device_id'],
            'action_time'   => $item['pay_time'],
            'type'          => $this->getType(),
            'cp_channel_id' => $item['promotion_id'],
            'request_id'    => '',
            'ip'            => '',
            'extend'        => $extend,
            'data'          => $item,
            'action_id'     => $item['trade_no']
        ];
    }


}
