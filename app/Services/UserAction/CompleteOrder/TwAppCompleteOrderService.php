<?php

namespace App\Services\UserAction\CompleteOrder;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\TwApp;
use App\Traits\ProductType\App;
use App\Traits\UserAction\CompleteOrder;


class TwAppCompleteOrderService extends UserActionAbstract
{
    use App;
    use TwApp;
    use CompleteOrder;


    public function get(array $product, string $startTime,string $endTime): array
    {
        $sdk = $this->getSdk($product);

        $data = [];
        $tmp  =  $sdk->getOrders([
            'is_pay'            => 1,
            'start_create_date'  => $startTime,
            'end_create_date'  => $endTime,
        ]);

        foreach ($tmp['data'] as $item){

            $data[] = $this->itemFilter($item);
        }


        return $data;
    }


    public function itemFilter($item): array
    {
        return [
            'open_id'       => $item['user_id'],
            'action_time'   => $item['finished_at'],
            'type'          => $this->getType(),
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => '',
            'ip'            => '',
            'data'          => $item,
            'action_id'     => $item['order_id'],
            'extend'        => array_merge([
                'order_id'      => $item['order_id']
            ],$this->filterExtendInfo($item)),
        ];
    }


}
