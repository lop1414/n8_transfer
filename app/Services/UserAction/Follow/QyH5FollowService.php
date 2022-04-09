<?php

namespace App\Services\UserAction\Follow;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\QyH5;
use App\Traits\ProductType\H5;
use App\Traits\UserAction\Follow;


class QyH5FollowService extends UserActionAbstract
{
    use H5;
    use QyH5;
    use Follow;


    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);

        $dates = array_unique([
            date('Y-m-d',strtotime($startTime)),
            date('Y-m-d',strtotime($endTime))
        ]);
        $data = [];

        foreach ($dates as $date){
            $page = 1;

            do{
                $tmp = $sdk->getUsers($date,$page);
                foreach ($tmp['data'] as $item){
                    if($item['is_subscribe'] == 1){
                        $data[] =  $this->itemFilter($item);
                    }
                }
                $page += 1;

            }while($page <= $tmp['last_page']);
        }

        return $data;
    }



    public function itemFilter($item): array
    {
        return [
            'open_id'       => $item['openid'],
            'action_time'   => date('Y-m-d H:i:s',$item['subscribe_time']),
            'type'          => $this->getType(),
            'cp_channel_id' => $item['follow_referral_id'],
            'request_id'    => '',
            'ip'            => $item['register_ip'],
            'extend'        => array_merge(['id'=> $item['id']],$this->filterExtendInfo($item)),
            'data'          => $item,
            'action_id'     => $item['openid']
        ];
    }


}
