<?php

namespace App\Services\UserAction\CompleteOrder;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Qr;
use App\Traits\ProductType\WeChatMiniProgram;
use App\Traits\UserAction\CompleteOrder;


class QrWeChatMiniProgramCompleteOrderService extends UserActionAbstract
{
    use WeChatMiniProgram;
    use Qr;
    use CompleteOrder;

    protected $productMap;



    public function setProductMap($arr){
        $this->productMap = $arr;
        return $this;
    }

    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);

        $data = [];
        $page = 1;
        do{
            $list =  $sdk->getOrders($startTime,$endTime,$page);

            foreach ($list['orderList'] as $item){
                if($item['status'] == 1) {
                    $data[] = $this->itemFilter($item);
                }
            }

            $page += 1;
            $number = count($data);
        }while($number < $list['total']);

        return $data;
    }


    public function itemFilter($item): array
    {
        return [
            'product_id'    => $this->productMap[$item['pack_appid']],
            'open_id'       => $item['user_id'],
            'action_time'   => $item['pay_time'],
            'type'          => $this->getType(),
            'cp_channel_id' => $item['link_id'],
            'request_id'    => '',
            'ip'            => '',
            'data'          => $item,
            'action_id'     => $item['order_num'],
            'extend'        => array_merge([
                'order_id'      => $item['order_num']
            ],$this->filterExtendInfo($item)),
        ];
    }


}
