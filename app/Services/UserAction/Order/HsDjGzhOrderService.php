<?php

namespace App\Services\UserAction\Order;

use App\Services\UserAction\CompleteOrder\HsDjGzhCompleteOrderService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Hs;
use App\Traits\ProductType\DjGzh;
use App\Traits\UserAction\Order;


class HsDjGzhOrderService extends UserActionAbstract
{
    use DjGzh;
    use Hs;
    use Order;

    protected $hsDjGzhCompleteOrderService;

    public function __construct()
    {
        $this->hsDjGzhCompleteOrderService = new HsDjGzhCompleteOrderService();
    }


    public function getTotal(array $product, string $startTime, string $endTime): ?int
    {
        $sdk = $this->getSdk($product);
        $param = [
            'search_date' => $startTime.' - '.$endTime,
            'applet_id' => $product['extends']['applet_id'],
            'show_id' => $product['extends']['show_id'],
            'channel_id' => $product['cp_product_alias'],
        ];
        $orders =  $sdk->getOrders($param);
        return $orders['count'] ?? null;
    }


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
                $data[] = [
                    'open_id'       => $item['user_id'],
                    'action_time'   => $item['request_at'],
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['spread_id'],
                    'request_id'    => '',
                    'ip'            => '',
                    'data'          => $item,
                    'action_id'     => $item['order_num'],
                    'extend'        => array_merge([
                        'amount'        => $item['total_charge_amount'] * 100,
                        'type'          => $this->getOrderType($item['charge_type']),
                        'order_id'      => $item['order_num']
                    ],$this->filterExtendInfo($item)),
                ];

                //支付
                if($item['order_status'] == 1){
                    $data[] = $this->hsDjGzhCompleteOrderService->itemFilter($item);
                }
            }

            $page += 1;

        }while(count($data) < $orders['count']);

        return $data;
    }


}
