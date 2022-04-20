<?php

namespace App\Services\UserAction\Reg;

use App\Services\UserAction\UserActionAbstract;

use App\Traits\Cp\Yw;
use App\Traits\ProductType\Kyy;
use App\Traits\UserAction\Reg;


class YwKyyRegService extends UserActionAbstract
{
    use Kyy;
    use Yw;
    use Reg;


    public function getTotal(array $product, string $startTime, string $endTime): ?int
    {
        $ywSdk = $this->getSdk($product);
        $tmp = $ywSdk->getUser([
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

            $tmp = $ywSdk->getUser($reqPara);

            foreach ($tmp['list'] as $item){

                $data[] = [
                    'open_id'       => $item['guid'],
                    'action_time'   => $item['seq_time'],
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['channel_id'],
                    'request_id'    => '',
                    'ip'            => '',
                    'action_id'     => $item['guid'],
                    'extend'        => $this->filterExtendInfo($item),
                    'data'          => $item
                ];
            }
            $reqPara['page'] += 1;
            $reqPara['next_id'] = $tmp['next_id'];

        }while(count($data) < $tmp['total_count']);

        return array_merge($data);
    }


}
