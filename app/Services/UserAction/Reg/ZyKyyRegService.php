<?php

namespace App\Services\UserAction\Reg;

use App\Services\UserAction\AddShortcut\ZyKyyAddShortcutService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Zy;
use App\Traits\ProductType\Kyy;
use App\Traits\UserAction\Reg;


class ZyKyyRegService extends UserActionAbstract
{
    use Kyy;
    use Zy;
    use Reg;

    protected $zyKyyAddShortcutService;

    public function __construct()
    {
        $this->zyKyyAddShortcutService = new ZyKyyAddShortcutService();
    }

    public function get(array $product, string $startTime,string $endTime): array
    {

        $zySdk = $this->getSdk($product);

        $data = [];

        $page = 1;
        do{

            $tmp =  $zySdk->getUsers([
                'start_time'  => $startTime,
                'page'  => $page
            ]);
            foreach ($tmp['list'] as $item){
                $data[] = [
                    'open_id'       => $item['id'],
                    'action_time'   => $item['reg_time'],
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['channel_id'],
                    'request_id'    => '',
                    'ip'            => $item['ip'],
                    'action_id'     => $item['id'],
                    'extend'        => $this->filterExtendInfo($item),
                    'data'          => $item
                ];
                // 加桌
                if($item['is_save_shortcuts'] == 1){
                    $data[] = $this->zyKyyAddShortcutService->itemFilter($item);
                }
            }
            $page += 1;

        }while($tmp['paginate']['pagenumber'] < $tmp['paginate']['totalnumber']);

        return $data;
    }


}
