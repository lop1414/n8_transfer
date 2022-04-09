<?php

namespace App\Services\UserAction\Reg;


use App\Services\UserAction\Follow\QyH5FollowService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\QyH5;
use App\Traits\ProductType\H5;
use App\Traits\UserAction\Reg;


class QyH5RegService extends UserActionAbstract
{
    use H5;
    use QyH5;
    use Reg;

    protected $qyH5FollowService;

    public function __construct()
    {
        $this->qyH5FollowService = new QyH5FollowService();
    }


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
                    $data[] = [
                        'open_id'       => $item['openid'],
                        'action_time'   => date('Y-m-d H:i:s',$item['createtime']),
                        'type'          => $this->getType(),
                        'cp_channel_id' => $item['follow_referral_id'],
                        'request_id'    => '',
                        'ip'            => $item['register_ip'],
                        'action_id'     => $item['openid'],
                        'extend'        => array_merge([
                            'id'      => $item['id']
                        ],$this->filterExtendInfo($item)),
                        'data' => $item
                    ];

                    // 关注
                    if($item['is_subscribe'] == 1){
                        $data[] =  $this->qyH5FollowService->itemFilter($item);
                    }
                }
                $page += 1;

            }while($page <= $tmp['last_page']);
        }

        return $data;
    }


}
