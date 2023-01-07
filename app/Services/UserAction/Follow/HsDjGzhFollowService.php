<?php

namespace App\Services\UserAction\Follow;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Hs;
use App\Traits\ProductType\DjGzh;
use App\Traits\UserAction\Follow;


class HsDjGzhFollowService extends UserActionAbstract
{
    use DjGzh;
    use Hs;
    use Follow;


    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);
        $param = [
            'search_date' => $startTime.' - '.$endTime,
            'applet_id' => $product['extends']['applet_id'],
            'show_id' => $product['extends']['show_id'],
            'channel_id' => $product['cp_product_alias'],
        ];

        $data = [];
        $page = 1;
        do{
            $param['page'] = $page;
            $tmp =  $sdk->getUsers($param);
            foreach ($tmp['data'] as $item){
               if($item['subscribed_at'] != '-'){
                   $data[] = $this->itemFilter($item);
               }
            }
            $page += 1;

        }while(count($data) < $tmp['count']);
    }



    public function itemFilter($item): array
    {

        return [
            'open_id'       => $item['user_id'],
            'action_time'   => $item['subscribed_at'],
            'type'          => $this->getType(),
            'cp_channel_id' => $item['spread_id'],
            'request_id'    => '',
            'ip'            => '',
            'extend'        => $this->filterExtendInfo($item),
            'data'          => $item,
            'action_id'     => $item['user_id']
        ];
    }


}
