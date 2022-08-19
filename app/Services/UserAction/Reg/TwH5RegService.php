<?php

namespace App\Services\UserAction\Reg;

use App\Services\UserAction\Follow\TwH5FollowService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\TwH5;
use App\Traits\ProductType\H5;
use App\Traits\UserAction\Reg;


class TwH5RegService extends UserActionAbstract
{
    use H5;
    use TwH5;
    use Reg;

    protected $twH5FollowService;

    public function __construct()
    {
        $this->twH5FollowService = new TwH5FollowService();
    }

    public function get(array $product, string $startTime,string $endTime): array
    {

        $twSdk = $this->getSdk($product);

        $date = date('Y-m-d',strtotime($startTime));
        $endDate = date('Y-m-d',strtotime($endTime));
        $data = [];

        do{
            $page = 1;
            $total = 0;
            do{
                $tmp =  $twSdk->getUsers([
                    'date'  => $date,
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
                        $data[] = $this->twH5FollowService->itemFilter($item);
                    }
                }

                $page += 1;
            }while($total < $tmp['count']);

            $date = date('Y-m-d',strtotime('+1 days',strtotime($date)));

        }while($date <= $endDate);
        return $data;
    }


}
