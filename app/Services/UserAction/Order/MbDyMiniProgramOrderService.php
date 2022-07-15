<?php

namespace App\Services\UserAction\Order;

use App\Common\Enums\OrderTypeEnums;
use App\Services\UserAction\CompleteOrder\MbDyMiniProgramCompleteOrderService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Mb;
use App\Traits\ProductType\DyMiniProgram;
use App\Traits\UserAction\Order;


class MbDyMiniProgramOrderService extends UserActionAbstract
{
    use DyMiniProgram;
    use Mb;
    use Order;

    protected $mbDyMiniProgramCompleteOrderService;

    public function __construct()
    {
        $this->mbDyMiniProgramCompleteOrderService = new MbDyMiniProgramCompleteOrderService();
    }


    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);

        $data = [];
        $page = 1;
        do{
            $tmp =  $sdk->getOrders($startTime, $endTime, $page);
            foreach ($tmp['items'] as $item){
                $data[] = [
                    'open_id'       => $item['memberId'],
                    'action_time'   => $item['createDate'],
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['promotionId'],
                    'request_id'    => '',
                    'ip'            => $item['ip'],
                    'data'          => $item,
                    'action_id'     => $item['id'],
                    'extend'        => array_merge([
                        'amount'        => intval($item['payNotifyAmount'] * 100),
                        'type'          => OrderTypeEnums::NORMAL,
                        'order_id'      => $item['id']
                    ],$this->filterExtendInfo($item)),
                ];

                if($item['payState'] == 1){
                    $data[] = $this->mbDyMiniProgramCompleteOrderService->itemFilter($item);
                }
            }

            $page += 1;

        }while($page <= $tmp['totalPages']);

        return $data;
    }


}
