<?php

namespace App\Services\UserAction\Order;

use App\Services\UserAction\CompleteOrder\TwH5CompleteOrderService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\TwH5;
use App\Traits\ProductType\H5;
use App\Traits\UserAction\Order;


class TwH5OrderService extends UserActionAbstract
{
    use H5;
    use TwH5;
    use Order;

    protected $twH5CompleteOrderService;

    public function __construct()
    {
        $this->twH5CompleteOrderService = new TwH5CompleteOrderService();
    }


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

                foreach ($tmp['data'] as $item) {
                    $total += 1;

                    $data[] = [
                        'open_id' => $item['uid'],
                        'action_time' => $item['create_time'],
                        'type' => $this->getType(),
                        'cp_channel_id' => $item['spread_id'],
                        'request_id' => '',
                        'ip' => '',
                        'data' => $item,
                        'action_id' => $item['id'],
                        'extend' => array_merge([
                            'amount' => $item['amount'] * 100,
                            'type' => $this->getOrderType($item['pay_type']),
                            'order_id' => $item['id']
                        ], $this->filterExtendInfo($item)),
                    ];

                    // 完成订单
                    if ($item['is_pay'] == 1) {
                        $data[] = $this->twH5CompleteOrderService->itemFilter($item);
                    }
                }


            }while($total < $tmp['count']);
            $date = date('Y-m-d',strtotime('+1 days',strtotime($date)));

        }while($date <= $endDate);

        return $data;
    }


}
