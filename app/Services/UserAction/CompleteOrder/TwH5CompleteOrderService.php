<?php

namespace App\Services\UserAction\CompleteOrder;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\TwH5;
use App\Traits\ProductType\H5;
use App\Traits\UserAction\CompleteOrder;


class TwH5CompleteOrderService extends UserActionAbstract
{
    use H5;
    use TwH5;
    use CompleteOrder;


    public function get(array $product, string $startTime,string $endTime): array
    {
        $twSdk = $this->getSdk($product);

        $date = date('Y-m-d',strtotime($startTime));
        $endDate = date('Y-m-d',strtotime($endTime));
        $data = [];
        do {
            $page = 1;
            $total = 0;
            do {
                $tmp = $twSdk->getOrders([
                    'date' => $date,
                    'page' => $page
                ]);
                foreach ($tmp as $item) {
                    $total += 1;

                    if ($item['is_pay'] == 1) {
                        $data[] = $this->itemFilter($item);
                    }
                }
            }while($total < $tmp['count']);
            $date = date('Y-m-d',strtotime('+1 days',strtotime($date)));

        }while($date <= $endDate);

        return $data;
    }


    public function itemFilter($item): array
    {
        return [
            'open_id'       => $item['uid'],
            'action_time'   => $item['pay_time'],
            'type'          => $this->getType(),
            'cp_channel_id' => $item['spread_id'],
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
