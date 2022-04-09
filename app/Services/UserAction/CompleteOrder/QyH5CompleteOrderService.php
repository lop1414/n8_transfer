<?php

namespace App\Services\UserAction\CompleteOrder;


use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\QyH5;
use App\Traits\ProductType\H5;
use App\Traits\UserAction\CompleteOrder;


class QyH5CompleteOrderService extends UserActionAbstract
{
    use H5;
    use QyH5;
    use CompleteOrder;


    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);
        $dates = array_unique([
            date('Y-m-d',strtotime($startTime)),
            date('Y-m-d',strtotime($endTime))
        ]);
        $data = [];

        foreach ($dates as $date) {
            $page = 1;

            do {
                $tmp = $sdk->getOrders($date, $page);
                foreach ($tmp['data'] as $item) {
                    if ($item['state'] == 2) {
                        $data[] = $this->itemFilter($item);
                    }
                }

                $page += 1;

            }while ($page <= $tmp['last_page']);
        }
        return $data;
    }


    public function itemFilter($item): array
    {
        $channelId = '';
        if(!empty($item['referral_url'])){
            $ret = parse_url($item['referral_url']);
            parse_str($ret['query'], $urlParam);
            $channelId = $urlParam['referral_id'] ?? '';
        }

        $extend = array_merge(['order_id'=> $item['trade_no']],$this->filterExtendInfo($item));
        return [
            'open_id'       => $item['user_open_id'],
            'action_time'   => date('Y-m-d H:i:s',$item['finish_time']),
            'type'          => $this->getType(),
            'cp_channel_id' => $channelId,
            'request_id'    => '',
            'ip'            => '',
            'extend'        => $extend,
            'data'          => $item,
            'action_id'     => $item['trade_no']
        ];
    }


}
