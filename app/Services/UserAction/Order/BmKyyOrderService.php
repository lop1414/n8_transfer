<?php

namespace App\Services\UserAction\Order;

use App\Services\UserAction\CompleteOrder\BmKyyCompleteOrderService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Bm;
use App\Traits\ProductType\Kyy;
use App\Traits\UserAction\Order;


class BmKyyOrderService extends UserActionAbstract
{
    use Kyy;
    use Bm;
    use Order;

    protected $bmKyyCompleteOrderService;

    public function __construct()
    {
        $this->bmKyyCompleteOrderService = new BmKyyCompleteOrderService();
    }


    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);

        $data = [];
        $page = 1;
        do{
            $tmp =  $sdk->getOrders($startTime, $endTime, $page);
            foreach ($tmp['list'] as $item){
                $data[] = [
                    'open_id'       => $item['uuid'],
                    'action_time'   => date('Y-m-d H:i:s',$item['createTime']),
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['channelid'],
                    'request_id'    => '',
                    'ip'            => '',
                    'data'          => $item,
                    'action_id'     => $item['orderSn'],
                    'extend'        => array_merge([
                        'amount'        => $item['orderPrice'],
                        'type'          => $this->getOrderType($item['orderType']),
                        'order_id'      => $item['orderSn']
                    ],$this->filterExtendInfo($item)),
                ];

                if($item['orderStatus'] == 1){
                    $data[] = $this->bmKyyCompleteOrderService->itemFilter($item);
                }
            }

            $page += 1;

        }while($page <= $tmp['totalPage']);

        return $data;
    }


}
