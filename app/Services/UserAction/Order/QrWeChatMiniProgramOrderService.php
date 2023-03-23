<?php

namespace App\Services\UserAction\Order;

use App\Services\UserAction\CompleteOrder\QrWeChatMiniProgramCompleteOrderService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Qr;
use App\Traits\ProductType\WeChatMiniProgram;
use App\Traits\UserAction\Order;


class QrWeChatMiniProgramOrderService extends UserActionAbstract
{
    use WeChatMiniProgram;
    use Qr;
    use Order;

    protected $qrWeChatMiniProgramCompleteOrderService;

    public function __construct()
    {
        $this->qrWeChatMiniProgramCompleteOrderService = new QrWeChatMiniProgramCompleteOrderService();
    }


    public function get(array $product, string $startTime,string $endTime): array
    {
        $sdk = $this->getSdk($product);

        $page = 1;
        $data = [];
        do{
            $list =  $sdk->getOrders($startTime,$endTime,$page);
            foreach ($list['orderList'] as $item){

                $data[] = [
                    'open_id'       => $item['user_id'],
                    'action_time'   => $item['create_time'],
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['link_id'],
                    'request_id'    => '',
                    'ip'            => '',
                    'data'          => $item,
                    'action_id'     => $item['order_num'],
                    'extend'        => array_merge([
                        'amount'        => $item['price'],
                        'type'          => $this->getOrderType($item['order_type']),
                        'order_id'      => $item['order_num']
                    ],$this->filterExtendInfo($item)),
                ];

                if($item['status'] == 1){
                    $data[] = $this->qrWeChatMiniProgramCompleteOrderService->itemFilter($item);
                }
            }
            $page += 1;
            $number = count($data);
        }while($number < $list['total']);
        return $data;
    }


}
