<?php

namespace App\Services\UserAction\Reg;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Bmdj;
use App\Traits\ProductType\WeChatMiniProgram;
use App\Traits\UserAction\Reg;


class BmdjWeChatMIniProgramRegService extends UserActionAbstract
{
    use WeChatMiniProgram;
    use Bmdj;
    use Reg;


    public function getTotal(array $product, string $startTime, string $endTime): ?int
    {
        return  null;
    }


    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);

        $page = 1;
        $data = [];
        do{
            $list =  $sdk->getUsers($startTime,$endTime,$page);
            foreach ($list['list'] as $item){
                $item['ua'] = $item['regUa'];
                $item['ip'] = $item['regIp'];

                $data[] = [
                    'open_id'       => $item['uuid'],
                    'action_time'   => date('Y-m-d H:i:s',$item['regTime']),
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['channelid'],
                    'request_id'    => $item['reqid'],
                    'ip'            => $item['ip'],
                    'action_id'     => $item['uuid'],
                    'extend'        => $this->filterExtendInfo($item),
                    'data'          => $item
                ];
            }
            $page += 1;
        }while($list['page'] < $list['totalPage']);

        return $data;
    }


}
