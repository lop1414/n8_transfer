<?php

namespace App\Services\UserAction\Follow;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\TwH5;
use App\Traits\ProductType\H5;
use App\Traits\UserAction\Follow;


class TwH5FollowService extends UserActionAbstract
{
    use H5;
    use TwH5;
    use Follow;


    public function get(array $product, string $startTime,string $endTime): array
    {

        $twSdk = $this->getSdk($product);
        $date = date('Y-m-d',strtotime($startTime));
        $endDate = date('Y-m-d',strtotime($endTime));
        $data = [];
        do{
            $tmp =  $twSdk->getUsers([
                'date'  => $date
            ]);

            foreach ($tmp['list'] as $item){

                if($item['is_follow'] == 1){
                    $data[] = $this->itemFilter($item);
                }
            }
            $date = date('Y-m-d',strtotime('+1 days',strtotime($date)));

        }while($date <= $endDate);

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
            'ip'            => '',
            'extend'        => $this->filterExtendInfo($item),
            'data'          => $item,
            'action_id'     => $item['id']
        ];
    }


}
