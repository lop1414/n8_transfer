<?php

namespace App\Services\UserAction\Follow;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\ZyH5;
use App\Traits\ProductType\H5;
use App\Traits\UserAction\Follow;


class ZyH5FollowService extends UserActionAbstract
{
    use H5;
    use ZyH5;
    use Follow;


    public function get(array $product, string $startTime,string $endTime): array
    {

        $twSdk = $this->getSdk($product);
        $data = [];
        $page = 1;
        $total = 0;

        do{
            $tmp =  $twSdk->getUsers([
                'start_time'  => $startTime,
                'end_time'  => $endTime,
                'page'  => $page
            ]);

            foreach ($tmp['list'] as $item){
                $total += 1;

                if($item['is_follow'] == 1){
                    $data[] = $this->itemFilter($item);
                }
            }
            $page += 1;
        }while($total < $tmp['count']);
        
        return $data;
    }



    public function itemFilter($item): array
    {

        return [
            'open_id'       => $item['id'],
            'action_time'   => $item['follow_time'],
            'type'          => $this->getType(),
            'cp_channel_id' => $item['spread_id'],
            'request_id'    => '',
            'ip'            => $item['ip'],
            'extend'        => $this->filterExtendInfo($item),
            'data'          => $item,
            'action_id'     => $item['id']
        ];
    }


}
