<?php

namespace App\Services\UserAction\Reg;

use App\Services\UserAction\AddShortcut\FqKyyAddShortcutService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Fq;
use App\Traits\ProductType\Kyy;
use App\Traits\UserAction\Reg;


class FqKyyRegService extends UserActionAbstract
{
    use Kyy;
    use Fq;
    use Reg;

    protected $fqKyyAddShortcutService;

    public function __construct()
    {
        $this->fqKyyAddShortcutService = new FqKyyAddShortcutService();
    }


    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);

        $page = 0;
        $data = [];
        $pageSize = 100;
        do{
            $list =  $sdk->getUserList($startTime,$endTime,$page,$pageSize);
            foreach ($list['data'] as $item){
                $item['ua'] = $item['user_agent'] ?? '';
                $item['click_id'] = $item['clickid'];
                $data[] = [
                    'open_id'       => $item['encrypted_device_id'],
                    'action_time'   => $item['register_time'],
                    'type'          => $this->getType(),
                    'cp_channel_id' => $item['promotion_id'],
                    'request_id'    => $advData['data']['request_id'] ?? '',
                    'ip'            => $item['ip'] ?? '',
                    'action_id'     => $item['encrypted_device_id'],
                    'extend'        => $this->filterExtendInfo($item),
                    'data'          => $item
                ];

                //加桌
                if($item['timestamp'] > 0){
                    $item['product_id'] = $product['id'];
                    $tmpData = $this->fqKyyAddShortcutService->itemFilter($item);
                    if(!empty($tmpData)){
                        $data[] = $tmpData;
                    }
                }
            }
            $page += 1;
            $number = count($data);
        }while($number < $list['total']);
        return $data;
    }


}
