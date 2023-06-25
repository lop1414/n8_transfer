<?php

namespace App\Services\UserAction\Reg;

use App\Services\UserAction\Follow\HsDjGzhFollowService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Hs;
use App\Traits\ProductType\DjGzh;
use App\Traits\UserAction\Reg;


class HsDjGzhRegService extends UserActionAbstract
{
    use DjGzh;
    use Hs;
    use Reg;


    protected $hsDjGzhFollowService;

    public function __construct()
    {
        $this->hsDjGzhFollowService = new HsDjGzhFollowService();
    }


    public function get(array $product, string $startTime,string $endTime): array
    {
        $sdk = $this->getSdk($product);
        $param = [
            'search_dyeing_date' => $startTime.' - '.$endTime,
            'applet_id' => $product['extends']['applet_id'],
            'show_id' => $product['extends']['show_id'],
            'channel_id' => $product['cp_product_alias'],
        ];

        $data = [];
        $page = 1;
        do{
            $param['page'] = $page;
            $users =  $sdk->getUsers($param);
            foreach ($users['data'] as $item){
//                $tmp = $sdk->readUserIpUa($item['user_id']);
//                $item['ip'] = $tmp['ip'];
//                $item['ua'] = $tmp['ua'];
                $data[] = [
                    'open_id'       => $item['user_id'],
                    'action_time'   => $item['dyeing_at'],
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['spread_id'],
                    'request_id'    => '',
                    'ip'            => $item['ip'],
                    'action_id'     => $item['user_id'],
                    'extend'        => $this->filterExtendInfo($item),
                    'data'          => $item
                ];

                //关注
                if($item['subscribed_at'] != '-'){
                    $data[] = $this->hsDjGzhFollowService->itemFilter($item);
                }
            }
            $page += 1;

        }while(count($data) < $users['count']);


        return $data;
    }
}
