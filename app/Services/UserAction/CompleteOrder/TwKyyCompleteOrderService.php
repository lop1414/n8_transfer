<?php

namespace App\Services\UserAction\CompleteOrder;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Tw;
use App\Traits\ProductType\Kyy;
use App\Traits\UserAction\CompleteOrder;


class TwKyyCompleteOrderService extends UserActionAbstract
{
    use Kyy;
    use Tw;
    use CompleteOrder;


    public function get(array $product, string $startTime,string $endTime): array
    {
        $twSdk = $this->getSdk($product);

        $dateTime = date('Y-m-d H:i',strtotime($startTime));
        $endTime = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($endTime)));
        $data = [];
        do{
            $tmp =  $twSdk->getOrders([
                'is_pay'    => 1,
                'pay_time'  => $dateTime
            ]);
            foreach ($tmp as $item){
                $data[] = $this->itemFilter($item);
            }
            $dateTime = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($dateTime)));

        }while($dateTime <= $endTime);

        return $data;
    }


    public function itemFilter($item): array
    {
        return [
            'open_id'       => $item['uid'],
            'action_time'   => $item['finished_at'],
            'type'          => $this->getType(),
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => '',
            'ip'            => '',
            'data'          => $item,
            'action_id'     => $item['id'],
            'extend'        => array_merge([
                'order_id'      => $item['id']
            ],$this->filterExtendInfo($item)),
        ];
    }


}
