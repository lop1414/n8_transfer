<?php

namespace App\Services\UserAction\Order;

use App\Services\UserAction\CompleteOrder\YwdjWeChatMiniProgramCompleteOrderService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Ywdj;
use App\Traits\ProductType\WeChatMiniProgram;
use App\Traits\UserAction\Order;


class YwdjWeChatMiniProgramOrderService extends UserActionAbstract
{
    use WeChatMiniProgram;
    use Ywdj;
    use Order;

    protected $ywdjWeChatMiniProgramCompleteOrderService;

    public function __construct()
    {
        $this->ywdjWeChatMiniProgramCompleteOrderService = new YwdjWeChatMiniProgramCompleteOrderService();
    }


    public function getTotal(array $product, string $startTime, string $endTime): ?int
    {
        $ywSdk = $this->getSdk($product);
        $tmp = $ywSdk->getOrdersByTime($product['cp_product_alias'],$startTime,$endTime);
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
            $tmp = $ywSdk->getOrdersByTime($product['cp_product_alias'],$startTime,$endTime,$page,$lastMinId,$lastMaxId,$totalCount);

            $list = $tmp['list'] ?: [];

            foreach ($list as $item){
                $currentTotal += 1;
                $data[] = [
                    'open_id'       => $item['guid'],
                    'action_time'   => $item['order_time'],
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['channel_id'],
                    'request_id'    => '',
                    'ip'            => '',
                    'data'          => $item,
                    'action_id'     => $item['yworder_id'],
                    'extend'        => array_merge([
                        'amount'        => $item['amount'] * 100,
                        'type'          => $this->getOrderType($item['order_type']),
                        'order_id'      => $item['yworder_id']
                    ],$this->filterExtendInfo($item)),
                ];

                // 完成订单
                if($item['order_status'] == 2){
                    $data[] = $this->ywdjWeChatMiniProgramCompleteOrderService->itemFilter($item);
                }
            }

            $lastMinId = $tmp['min_id'] ?? null;
            $lastMaxId = $tmp['max_id'] ?? null;
            $totalCount = $tmp['total_count'] ?? null;
            $page += 1;

        }while($currentTotal < $tmp['total_count']);

        if(!empty($data)){
            dump($data);
        }

        return $data;
    }


}
