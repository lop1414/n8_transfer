<?php

namespace App\Services\UserAction\CompleteOrder;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Hs;
use App\Traits\ProductType\DjGzh;
use App\Traits\UserAction\CompleteOrder;


class HsDjGzhCompleteOrderService extends UserActionAbstract
{
    use DjGzh;
    use Hs;
    use CompleteOrder;


    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);
        $param = [
            'search_date' => $startTime.' - '.$endTime,
            'applet_id' => $product['extends']['applet_id'],
            'show_id' => $product['extends']['show_id'],
            'channel_id' => $product['cp_product_alias'],
        ];

        $data = [];
        $page = 1;
        do{
            $param['page'] = $page;
            $orders =  $sdk->getOrders($param);

            foreach ($orders['data'] as $item){
                if($item['order_status'] == 1) {
                    $data[] = $this->itemFilter($item);
                }
            }

            $page += 1;

        }while(count($data) < $orders['count']);

        return $data;
    }


    public function itemFilter($item): array
    {
        return [
            'open_id'       => $item['user_id'],
            'action_time'   => $item['pay_at'],
            'type'          => $this->getType(),
            'cp_channel_id' => $item['spread_id'],
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
