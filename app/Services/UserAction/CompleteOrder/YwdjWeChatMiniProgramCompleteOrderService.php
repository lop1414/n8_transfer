<?php

namespace App\Services\UserAction\CompleteOrder;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Ywdj;
use App\Traits\ProductType\WeChatMiniProgram;
use App\Traits\UserAction\CompleteOrder;


class YwdjWeChatMiniProgramCompleteOrderService extends UserActionAbstract
{
    use WeChatMiniProgram;
    use Ywdj;
    use CompleteOrder;


    public function getTotal(array $product, string $startTime, string $endTime): ?int
    {
        $ywSdk = $this->getSdk($product);
        $tmp = $ywSdk->getOrdersByTime($product['cp_product_alias'],$startTime,$endTime,1,null,null,null,2);
        return $tmp['total_count'] ?? null;
    }


    public function get(array $product, string $startTime,string $endTime): array
    {
        $ywSdk = $this->getSdk($product);
        $page = 1;
        $data = [];
        $lastMinId = $lastMaxId = $totalCount = null;
        $currentTotal = 0;

        do{

            $tmp = $ywSdk->getOrdersByTime($product['cp_product_alias'],$startTime,$endTime,$page,$lastMinId,$lastMaxId,$totalCount,2);
            $list = $tmp['list'] ?: [];
            foreach ($list as $item){
                $currentTotal += 1;
                $data[] = $this->itemFilter($item);
            }

            $lastMinId = $tmp['min_id'] ?? null;
            $lastMaxId = $tmp['max_id'] ?? null;
            $totalCount = $tmp['total_count'] ?? null;
            $page += 1;

        }while($currentTotal < $tmp['total_count']);

        return $data;
    }


    public function itemFilter($item): array
    {
        $extend = array_merge(['order_id'=> $item['yworder_id']],$this->filterExtendInfo($item));
        return [
            'open_id'       => $item['guid'],
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
