<?php

namespace App\Services\UserAction\Order;

use App\Services\UserAction\CompleteOrder\QyH5CompleteOrderService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\QyH5;
use App\Traits\ProductType\H5;
use App\Traits\UserAction\Order;


class QyH5OrderService extends UserActionAbstract
{
    use H5;
    use QyH5;
    use Order;

    protected $qyH5CompleteOrderService;

    public function __construct()
    {
        $this->qyH5CompleteOrderService = new QyH5CompleteOrderService();
    }


    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);
        $dates = array_unique([
            date('Y-m-d',strtotime($startTime)),
            date('Y-m-d',strtotime($endTime))
        ]);
        $data = [];

        foreach ($dates as $date){
            $page = 1;

            do{
                $tmp = $sdk->getOrders($date,$page);
                foreach ($tmp['data'] as $item){

                    $channelId = '';
                    if(!empty($item['referral_url'])){
                        $ret = parse_url($item['referral_url']);
                        parse_str($ret['query'], $urlParam);
                        $channelId = $urlParam['referral_id'] ?? '';
                    }

                    $data[] = [
                        'open_id'       => $item['user_open_id'],
                        'action_time'   => date('Y-m-d H:i:s',$item['create_time']),
                        'type'          => $this->getType(),
                        'cp_channel_id' => $channelId,
                        'request_id'    => '',
                        'ip'            => '',
                        'data'          => $item,
                        'action_id'     => $item['trade_no'],
                        'extend'        => array_merge([
                            'amount'        => $item['money'] * 100,
                            'type'          => $this->getOrderType($item['order_type']),
                            'order_id'      => $item['trade_no']
                        ],$this->filterExtendInfo($item)),
                    ];

                    // 完成订单
                    if($item['state'] == 2){
                        $data[] = $this->qyH5CompleteOrderService->itemFilter($item);
                    }
                }
                $page += 1;

            }while($page <= $tmp['last_page']);
        }

        return $data;
    }


}
