<?php

namespace App\Services\UserAction\CompleteOrder;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Mb;
use App\Traits\ProductType\DyMiniProgram;
use App\Traits\UserAction\CompleteOrder;


class MbDyMiniProgramCompleteOrderService extends UserActionAbstract
{
    use DyMiniProgram;
    use Mb;
    use CompleteOrder;


    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);

        $data = [];
        $page = 1;
        do{
            $tmp =  $sdk->getOrders($startTime, $endTime, $page);
            foreach ($tmp['items'] as $item){
                if($item['payState'] == 1) {
                    $data[] = $this->itemFilter($item);
                }
            }

            $page += 1;

        }while($page <= $tmp['totalPages']);

        return $data;
    }


    public function itemFilter($item): array
    {
        return [
            'open_id'       => $item['memberId'],
            'action_time'   => $item['createDate'],
            'type'          => $this->getType(),
            'cp_channel_id' => $item['promotionId'],
            'request_id'    => '',
            'ip'            => $item['ip'],
            'data'          => $item,
            'action_id'     => $item['id'],
            'extend'        => array_merge([
                'order_id'      => $item['id']
            ],$this->filterExtendInfo($item)),
        ];
    }


}
