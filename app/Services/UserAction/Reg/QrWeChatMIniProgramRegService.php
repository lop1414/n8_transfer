<?php

namespace App\Services\UserAction\Reg;

use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Qr;
use App\Traits\ProductType\WeChatMiniProgram;
use App\Traits\UserAction\Reg;


class QrWeChatMIniProgramRegService extends UserActionAbstract
{
    use WeChatMiniProgram;
    use Qr;
    use Reg;


    public function getTotal(array $product, string $startTime, string $endTime): ?int
    {
        $sdk = $this->getSdk($product);
        $tmp = $sdk->getUserList($startTime,$endTime);
        return $tmp['total'] ?? null;
    }


    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);

        $page = 1;
        $data = [];
        $productMap = $this->getQrProductMap();
        do{
            $list =  $sdk->getUserList($startTime,$endTime,$page);
            foreach ($list['userList'] as $item){
                $item['ua'] = base64_decode($item['user_agent']);

                $data[] = [
                    'product_id'    => $productMap[$item['pack_appid']]['id'],
                    'open_id'       => $item['user_id'],
                    'action_time'   => $item['user_time'],
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['link_id'],
                    'request_id'    => '',
                    'ip'            => $item['ip'],
                    'action_id'     => $item['user_id'],
                    'extend'        => $this->filterExtendInfo($item),
                    'data'          => $item
                ];
            }
            $page += 1;
            $number = count($data);
        }while($number < $list['total']);
        return $data;
    }


}
