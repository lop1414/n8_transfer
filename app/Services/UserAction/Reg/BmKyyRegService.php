<?php

namespace App\Services\UserAction\Reg;

use App\Services\UserAction\AddShortcut\BmKyyAddShortcutService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Bm;
use App\Traits\ProductType\Kyy;
use App\Traits\UserAction\Reg;


class BmKyyRegService extends UserActionAbstract
{
    use Kyy;
    use Bm;
    use Reg;

    protected $bmKyyAddShortcutService;

    public function __construct()
    {
        $this->bmKyyAddShortcutService = new BmKyyAddShortcutService();
    }

    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);


        $data = [];
        $page = 1;
        do{
            $tmp =  $sdk->getChangeChannelLog($startTime, $endTime, $page);
            foreach ($tmp['list'] as $item){
                $ip = $item['clientIp'] ?? '';
                if(!empty($ip)){
                    $ip = $this->isIpv6($ip) ? $ip :long2ip($ip);
                }

                $data[] = [
                    'open_id'       => $item['uuid'],
                    'action_time'   => $item['createTime'],
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['channelid'],
                    'request_id'    => '',
                    'ip'            => $ip,
                    'action_id'     => $item['uuid'],
                    'extend'        => $this->filterExtendInfo($item),
                    'data'          => $item
                ];

                // 加桌
                if($item['isInstall'] == 1){
                    $data[] = $this->bmKyyAddShortcutService->itemFilter($item);
                }
            }
            $page += 1;

        }while($page <= $tmp['totalPage']);


        return $data;
    }


}
