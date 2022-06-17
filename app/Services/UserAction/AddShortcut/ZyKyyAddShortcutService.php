<?php

namespace App\Services\UserAction\AddShortcut;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Zy;
use App\Traits\ProductType\Kyy;
use App\Traits\UserAction\AddShortcut;


class ZyKyyAddShortcutService extends UserActionAbstract
{

    use Kyy;
    use Zy;
    use AddShortcut;


    public function get(array $product, string $startTime,string $endTime): array
    {

        $zySdk = $this->getSdk($product);

        $date = date('Y-m-d',strtotime($startTime));
        $end = date('Y-m-d',strtotime($endTime));
        $data = [];
        do{
            $tmp =  $zySdk->getUsers([
                'start_time'  => $date
            ]);

            foreach ($tmp as $item){
                if($item['is_save_shortcuts'] == 1) {
                    $data[] = $this->itemFilter($item);
                }
            }
            $date = date('Y-m-d',strtotime('+1 days',strtotime($date)));

        }while($date <= $end);

        return $data;
    }



    public function itemFilter($item): array
    {
        return [
            'open_id'       => $item['id'],
            'action_time'   => $item['reg_time'],
            'type'          => $this->getType(),
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => '',
            'ip'            => $item['ip'],
            'action_id'     => $item['id'],
            'extend'        => $this->filterExtendInfo($item),
            'data'          => $item,
        ];
    }


}
