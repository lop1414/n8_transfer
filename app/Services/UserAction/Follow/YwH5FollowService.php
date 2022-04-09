<?php

namespace App\Services\UserAction\Follow;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\ProductType\H5;
use App\Traits\Cp\Yw;
use App\Traits\UserAction\Follow;


class YwH5FollowService extends UserActionAbstract
{
    use H5;
    use Yw;
    use Follow;


    public function get(array $product, string $startTime,string $endTime): array
    {

        $ywSdk = $this->getSdk($product);
        $reqPara = [
            'start_time'  => strtotime($startTime),
            'end_time'  => strtotime($endTime),
            'page'   => 1
        ];

        $data = [];
        do{

            $tmp = $ywSdk->getH5User($reqPara);

            foreach ($tmp['list'] as $item){

                if($item['is_subscribe'] == 1){
                    $data[] = $this->itemFilter($item);
                }

            }
            $reqPara['page'] += 1;
            $reqPara['next_id'] = $tmp['next_id'];

        }while(count($data) < $tmp['total_count']);

        return $data;
    }



    public function itemFilter($item): array
    {

        return [
            'open_id'       => $item['openid'],
            'action_time'   => $item['subscribe_time'],
            'type'          => $this->getType(),
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => '',
            'ip'            => '',
            'extend'        => array_merge(['guid'=> $item['guid']],$this->filterExtendInfo($item)),
            'data'          => $item,
            'action_id'     => $item['openid']
        ];
    }


}
