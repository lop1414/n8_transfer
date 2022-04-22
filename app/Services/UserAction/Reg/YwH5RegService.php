<?php

namespace App\Services\UserAction\Reg;

use App\Services\UserAction\Follow\YwH5FollowService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\ProductType\H5;
use App\Traits\Cp\Yw;
use App\Traits\UserAction\Reg;


class YwH5RegService extends UserActionAbstract
{
    use H5;
    use Yw;
    use Reg;

    protected $ywH5FollowService;

    public function __construct()
    {
        $this->ywH5FollowService = new YwH5FollowService();
    }


    public function getTotal(array $product, string $startTime, string $endTime): ?int
    {
        $ywSdk = $this->getSdk($product);
        $tmp = $ywSdk->getH5User([
            'start_time'  => strtotime($startTime),
            'end_time'  => strtotime($endTime),
            'page'   => 1
        ]);
        return $tmp['total_count'] ?? null;
    }


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

                $data[] = [
                    'open_id'       => $item['openid'],
                    'action_time'   => $item['create_time'],
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['channel_id'],
                    'request_id'    => '',
                    'ip'            => '',
                    'action_id'     => $item['openid'],
                    'extend'        => array_merge([
                        'guid'      => $item['guid']
                    ],$this->filterExtendInfo($item)),
                    'data' => $item
                ];

                //关注
                if($item['is_subscribe'] == 1){
                    $data[] = $this->ywH5FollowService->itemFilter($item);
                }
            }
            $reqPara['page'] += 1;
            $reqPara['next_id'] = $tmp['next_id'];

        }while(count($data) < $tmp['total_count']);

        return $data;
    }


}
