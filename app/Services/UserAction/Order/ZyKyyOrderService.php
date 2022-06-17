<?php

namespace App\Services\UserAction\Order;

use App\Common\Enums\OrderTypeEnums;
use App\Services\UserAction\CompleteOrder\ZyKyyCompleteOrderService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Zy;
use App\Traits\ProductType\Kyy;
use App\Traits\UserAction\Order;


class ZyKyyOrderService extends UserActionAbstract
{
    use Kyy;
    use Zy;
    use Order;

    protected $zyKyyCompleteOrderService;

    public function __construct()
    {
        $this->zyKyyCompleteOrderService = new ZyKyyCompleteOrderService();
    }


    public function get(array $product, string $startTime,string $endTime): array
    {


        $zySdk = $this->getSdk($product);

        $date = date('Y-m-d',strtotime($startTime));
        $end = date('Y-m-d',strtotime($endTime));
        $data = [];
        do{
            $page = 1;
            do{

                $tmp =  $zySdk->getOrders([
                    'start_time'  => $date,
                    'page'  => $page
                ]);
                foreach ($tmp['list'] as $item){
                    $data[] = [
                        'open_id'       => $item['uid'],
                        'action_time'   => $item['created_at'],
                        'type'          => $this->getType(),
                        'cp_channel_id' => $item['channel_id'],
                        'request_id'    => '',
                        'ip'            => '',
                        'data'          => $item,
                        'action_id'     => $item['id'],
                        'extend'        => array_merge([
                            'amount'        => $item['amount'],
                            'type'          => OrderTypeEnums::NORMAL,
                            'order_id'      => $item['id']
                        ],$this->filterExtendInfo($item)),
                    ];

                    // 加桌
                    if($item['is_pay'] == 1){
                        $data[] = $this->zyKyyCompleteOrderService->itemFilter($item);
                    }
                }
                $page += 1;

            }while($tmp['paginate']['pagenumber'] < $tmp['paginate']['totalnumber']);

            $date = date('Y-m-d',strtotime('+1 days',strtotime($date)));
        }while($date <= $end);

        return $data;
    }


}
