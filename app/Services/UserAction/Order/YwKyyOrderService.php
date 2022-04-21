<?php

namespace App\Services\UserAction\Order;

use App\Services\UserAction\CompleteOrder\YwKyyCompleteOrderService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\ProductType\Kyy;
use App\Traits\Cp\Yw;
use App\Traits\UserAction\Order;


class YwKyyOrderService extends UserActionAbstract
{
    use Kyy;
    use Yw;
    use Order;

    protected $ywKyyCompleteOrderService;

    public function __construct()
    {
        $this->ywKyyCompleteOrderService = new YwKyyCompleteOrderService();
    }


    public function getTotal(array $product, string $startTime, string $endTime): ?int
    {
        $ywSdk = $this->getSdk($product);
        $tmp = $ywSdk->getOrders([
            'start_time'  => strtotime($startTime),
            'end_time'  => strtotime($endTime),
            'page'   => 1
        ]);
        return $tmp['total_count'] ?? null;
    }


    public function get(array $product, string $startTime,string $endTime): array
    {

        $ywSdk = $this->getSdk($product);
        $reqPara = [
            'start_time'  => strtotime($startTime),
            'end_time'  => strtotime($endTime),
            'page'   => 1
        ];

        $data = [];

        do{

            $tmp = $ywSdk->getOrders($reqPara);

            foreach ($tmp['list'] as $item){

                $data[] = [
                    'open_id'       => $item['guid'],
                    'action_time'   => $item['order_time'],
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['channel_id'],
                    'request_id'    => '',
                    'ip'            => '',
                    'data'          => $item,
                    'action_id'     => $item['yworder_id'],
                    'extend'        => array_merge([
                        'amount'        => $item['amount'] * 100,
                        'type'          => $this->getOrderType($item['order_type']),
                        'order_id'      => $item['yworder_id']
                    ],$this->filterExtendInfo($item)),
                ];

                // 完成订单
                if($item['order_status'] == 2){
                    $data[] = $this->ywKyyCompleteOrderService->itemFilter($item);
                }
            }

            $reqPara['page'] += 1;
            $reqPara['last_min_id'] = $tmp['min_id'];
            $reqPara['last_max_id'] = $tmp['max_id'];
            $reqPara['total_count'] = $tmp['total_count'];
            $reqPara['last_page'] = $tmp['page'];

        }while(count($data) < $tmp['total_count']);

        return $data;
    }


}
