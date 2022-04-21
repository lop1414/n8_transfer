<?php

namespace App\Services\UserAction\AddShortcut;


use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Fq;
use App\Traits\ProductType\Kyy;
use App\Traits\UserAction\AddShortcut;


class FqKyyAddShortcutService extends UserActionAbstract
{
    use Kyy;
    use Fq;
    use AddShortcut;


    public function get(array $product, string $startTime,string $endTime): array
    {

        return [];
    }



    public function itemFilter($item): array
    {

        $saveData = [
            'product_id'    => $item['product_id'] ?? 0,
            'open_id'       => $item['encrypted_device_id'],
            'action_time'   => date('Y-m-d H:i:s',$item['timestamp']),
            'type'          => $this->getType(),
            'cp_channel_id' => $item['promotion_id'],
            'request_id'    => '',
            'ip'            => $item['ip'] ?? '',
            'data'          => $item,
            'action_id'     => $item['encrypted_device_id'],
            'extend'        => $this->filterExtendInfo($item),
        ];

        // 重复加桌
        if($this->isRepeatAddShortcut($saveData)){
            return [];
        }

        return $saveData;
    }


}
