<?php

namespace App\Services\UserAction\Reg;

use App\Services\UserAction\Follow\ZyH5FollowService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\ZyH5;
use App\Traits\ProductType\H5;
use App\Traits\UserAction\Reg;


class ZyH5RegService extends UserActionAbstract
{
    use H5;
    use ZyH5;
    use Reg;

    protected $zyH5FollowService;

    public function __construct()
    {
        $this->zyH5FollowService = new ZyH5FollowService();
    }

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

            foreach ($tmp['data'] as $item){
                $total += 1;
                $data[] = [
                    'open_id'       => $item['id'],
                    'action_time'   => $item['create_time'],
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['spread_id'],
                    'request_id'    => $advData['data']['request_id'] ?? '',
                    'ip'            => $item['ip'],
                    'action_id'     => $item['id'],
                    'extend'        => $this->filterExtendInfo($item),
                    'data'          => $item
                ];
                // 加桌
                if($item['is_follow'] == 1){
                    $data[] = $this->zyH5FollowService->itemFilter($item);
                }
            }

            $page += 1;
        }while($total < $tmp['count']);

        return $data;
    }


}
