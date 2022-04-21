<?php

namespace App\Services\UserAction\Reg;

use App\Services\UserAction\AddShortcut\TwKyyAddShortcutService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Tw;
use App\Traits\ProductType\Kyy;
use App\Traits\UserAction\Reg;


class TwKyyRegService extends UserActionAbstract
{
    use Kyy;
    use Tw;
    use Reg;

    protected $twKyyAddShortcutService;

    public function __construct()
    {
        $this->twKyyAddShortcutService = new TwKyyAddShortcutService();
    }

    public function get(array $product, string $startTime,string $endTime): array
    {

        $twSdk = $this->getSdk($product);

        $dateTime = date('Y-m-d H:i',strtotime($startTime));
        $endTime = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($endTime)));
        $data = [];
        do{
            $tmp =  $twSdk->getUsers([
                'reg_time'  => $dateTime
            ]);

            foreach ($tmp as $item){
                $data[] = [
                    'open_id'       => $item['id'],
                    'action_time'   => $item['reg_time'],
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['channel_id'],
                    'request_id'    => $advData['data']['request_id'] ?? '',
                    'ip'            => $item['device_ip'],
                    'action_id'     => $item['id'],
                    'extend'        => $this->filterExtendInfo($item),
                    'data'          => $item
                ];
                // 加桌
                if($item['is_save_shortcuts'] == 1){
                    $data[] = $this->twKyyAddShortcutService->itemFilter($item);
                }
            }
            $dateTime = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($dateTime)));

        }while($dateTime <= $endTime);

        return $data;
    }


}
