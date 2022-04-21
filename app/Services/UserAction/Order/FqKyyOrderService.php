<?php

namespace App\Services\UserAction\Order;

use App\Common\Enums\OrderTypeEnums;
use App\Services\UserAction\CompleteOrder\FqKyyCompleteOrderService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Fq;
use App\Traits\ProductType\Kyy;
use App\Traits\UserAction\Order;


class FqKyyOrderService extends UserActionAbstract
{
    use Kyy;
    use Fq;
    use Order;

    protected $fqKyyCompleteOrderService;

    public function __construct()
    {
        $this->fqKyyCompleteOrderService = new FqKyyCompleteOrderService();
    }


    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);
        $offset = 0;
        $data = [];
        do{
            $list =  $sdk->getOrders($startTime,$endTime,$offset);
            foreach ($list['result'] as $item){
                $data[] = [
                    'open_id'       => $item['device_id'],
                    'action_time'   => $item['create_time'],
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['promotion_id'],
                    'request_id'    => '',
                    'ip'            => '',
                    'data'          => $item,
                    'action_id'     => $item['trade_no'],
                    'extend'        => array_merge([
                        'amount'        => $item['pay_fee'],
                        'type'          => $item['is_activity']? OrderTypeEnums::ACTIVITY : OrderTypeEnums::ANNUAL,
                        'order_id'      => $item['trade_no']
                    ],$this->filterExtendInfo($item)),
                ];
                // 完成订单
                if($item['status'] == 0){
                    $data[] = $this->fqKyyCompleteOrderService->itemFilter($item);
                }
            }
            $offset = $list['next_offset'];

        }while($list['has_more']);
        return $data;
    }


}
