<?php

namespace App\Services\UserAction\CompleteOrder;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\ProductType\H5;
use App\Traits\Cp\Yw;
use App\Traits\UserAction\CompleteOrder;


class YwH5CompleteOrderService extends UserActionAbstract
{
    use H5;
    use Yw;
    use CompleteOrder;


    public function getTotal(array $product, string $startTime, string $endTime): ?int
    {
        $ywSdk = $this->getSdk($product);
        $tmp = $ywSdk->getH5Order([
            'order_status'  => 2,
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
            'order_status'  => 2,
            'start_time'  => strtotime($startTime),
            'end_time'  => strtotime($endTime),
            'page'   => 1
        ];

        $data = [];
        do{

            $tmp = $ywSdk->getOrders($reqPara);
            $data = array_merge($data,$tmp['list']);

            foreach ($tmp['list'] as $item){

                $data[] = $this->itemFilter($item);
            }

            $reqPara['page'] += 1;
            $reqPara['last_min_id'] = $tmp['min_id'];
            $reqPara['last_max_id'] = $tmp['max_id'];
            $reqPara['total_count'] = $tmp['total_count'];
            $reqPara['last_page'] = $tmp['page'];

        }while(count($data) < $tmp['total_count']);

        return $data;
    }


    public function itemFilter($item): array
    {
        $extend = array_merge(['order_id'=> $item['yworder_id']],$this->filterExtendInfo($item));
        return [
            'open_id'       => $item['openid'],
            'action_time'   => $item['pay_time'],
            'type'          => $this->getType(),
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => '',
            'ip'            => '',
            'extend'        => $extend,
            'data'          => $item,
            'action_id'     => $item['yworder_id']
        ];
    }


}
