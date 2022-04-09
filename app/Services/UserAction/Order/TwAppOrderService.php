<?php

namespace App\Services\UserAction\Order;

use App\Services\UserAction\CompleteOrder\TwAppCompleteOrderService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\TwApp;
use App\Traits\ProductType\App;
use App\Traits\UserAction\Order;


class TwAppOrderService extends UserActionAbstract
{
    use App;
    use TwApp;
    use Order;

    protected $twAppCompleteOrderService;

    public function __construct()
    {
        $this->twAppCompleteOrderService = new TwAppCompleteOrderService();
    }


    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);

        $data = [];
        $tmp  =  $sdk->getOrders([
            'start_create_date'  => $startTime,
            'end_create_date'  => $endTime,
        ]);

        foreach ($tmp['data'] as $item){

            $data[] = [
                'open_id'       => $item['user_id'],
                'action_time'   => $item['created_at'],
                'type'          => $this->getType(),
                'cp_channel_id' => $item['channel_id'],
                'request_id'    => '',
                'ip'            => '',
                'data'          => $item,
                'action_id'     => $item['order_id'],
                'extend'        => array_merge([
                    'amount'        => $item['amount']* 100,
                    'type'          => $this->getOrderType($item['type']),
                    'order_id'      => $item['order_id']
                ],$this->filterExtendInfo($item)),
            ];

            // 完成订单
            if($item['is_pay'] == 1){
                $data[] = $this->twAppCompleteOrderService->itemFilter($item);
            }
        }


        return $data;
    }


}
