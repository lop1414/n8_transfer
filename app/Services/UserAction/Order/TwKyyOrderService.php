<?php

namespace App\Services\UserAction\Order;

use App\Services\UserAction\CompleteOrder\TwKyyCompleteOrderService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Tw;
use App\Traits\ProductType\Kyy;
use App\Traits\UserAction\Order;


class TwKyyOrderService extends UserActionAbstract
{
    use Kyy;
    use Tw;
    use Order;

    protected $twKyyCompleteOrderService;

    public function __construct()
    {
        $this->twKyyCompleteOrderService = new TwKyyCompleteOrderService();
    }


    public function get(array $product, string $startTime,string $endTime): array
    {

        $twSdk = $this->getSdk($product);

        $dateTime = date('Y-m-d H:i',strtotime($startTime));
        $endTime = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($endTime)));
        $data = [];
        do{
            $tmp =  $twSdk->getOrders([
                'pay_time'  => $dateTime
            ]);
            foreach ($tmp as $item){
                $data[] = [
                    'open_id'       => $item['uid'],
                    'action_time'   => $item['created_at'],
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['channel_id'],
                    'request_id'    => '',
                    'ip'            => '',
                    'data'          => $item,
                    'action_id'     => $item['id'],
                    'extend'        => array_merge([
                        'amount'        => $item['amount'],
                        'type'          => $this->getOrderType($item['type']),
                        'order_id'      => $item['id']
                    ],$this->filterExtendInfo($item)),
                ];

                // 完成订单
                if($item['is_pay'] == 1){
                    $data[] = $this->twKyyCompleteOrderService->itemFilter($item);
                }
            }
            $dateTime = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($dateTime)));

        }while($dateTime <= $endTime);

        return $data;
    }


}
