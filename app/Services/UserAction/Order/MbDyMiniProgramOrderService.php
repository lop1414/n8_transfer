<?php

namespace App\Services\UserAction\Order;

use App\Common\Enums\OrderTypeEnums;
use App\Services\UserAction\CompleteOrder\MbDyMiniProgramCompleteOrderService;
use App\Services\UserAction\Reg\MbDyMiniProgramRegService;
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
        $regService = new MbDyMiniProgramRegService();
        do{
            $tmp =  $sdk->getOrders($startTime, $endTime, $page);
            foreach ($tmp['items'] as $item){
                // 注册信息
                $regTime = $item['memberCreateDate'];
                if(strtotime($item['createDate']) - strtotime($regTime) >= 24*60*60*3){
                    $regTime = $item['createDate'];
                    $item['has_change_reg_time'] = 1;
                }

                $data[] = $regService->itemFilter($item,$item['memberId'],$regTime);

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
