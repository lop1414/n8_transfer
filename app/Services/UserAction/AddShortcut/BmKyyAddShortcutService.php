<?php

namespace App\Services\UserAction\AddShortcut;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Bm;
use App\Traits\ProductType\Kyy;
use App\Traits\UserAction\AddShortcut;


class BmKyyAddShortcutService extends UserActionAbstract
{
    use Kyy;
    use Bm;
    use AddShortcut;

    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);

        $data = [];
        $page = 1;
        do{
            $tmp =  $sdk->getInstallUsers($startTime, $endTime, $page);
            foreach ($tmp['list'] as $item){
                // 加桌
                if($item['isInstall'] == 1){
                    $data[] = $this->itemFilter($item);
                }
            }

            $page += 1;

        }while($page <= $tmp['totalPage']);


        return $data;
    }



    public function itemFilter($item): array
    {
        return [
            'open_id'       => $item['uuid'],
            'action_time'   => date('Y-m-d H:i:s',$item['installTime']),
            'type'          => $this->getType(),
            'cp_channel_id' => $item['channelid'],
            'request_id'    => '',
            'ip'            => $item['regIp'] ?? '',
            'data'          => $item,
            'action_id'     => $item['uuid'],
            'extend'        => $this->filterExtendInfo($item),
        ];
    }


}
