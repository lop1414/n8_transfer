<?php

namespace App\Services\UserAction\Reg;

use App\Common\Tools\CustomException;
use App\Services\AdvClick\OceanClickService;
use App\Services\ChannelService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\Mb;
use App\Traits\ProductType\DyMiniProgram;
use App\Traits\UserAction\Reg;


class MbDyMiniProgramRegService extends UserActionAbstract
{
    use DyMiniProgram;
    use Mb;
    use Reg;

    protected $oceanClickService;
    protected $channelService;

    public function __construct()
    {
        $this->oceanClickService = new OceanClickService();
        $this->channelService = new ChannelService();
    }


    public function get(array $product, string $startTime,string $endTime): array
    {
        $sdk = $this->getSdk($product);


        $data = [];
        $page = 1;
        do{
            $tmp =  $sdk->getUsers($startTime, $endTime, $page);
            foreach ($tmp['items'] as $item){
                $data[] = $this->itemFilter($item,$item['id'],$item['createDate']);
            }
            $page += 1;

        }while($page <= $tmp['totalPages']);


        return $data;
    }



    public function itemFilter($item,$openId,$actionTime): array
    {
        $requestId = '';

        if(!empty($item['adid'])){
            $channel = $this->channelService->readChannelByCpChannelId($this->getCpType(),$item['promotionId']);
            if(empty($channel)){
                throw new CustomException([
                    'code' => 'NOT_FOUND_CHANNEL',
                    'message' => '找不到渠道',
                    'log'   => true,
                    'data' => $item,
                ]);
            }


            $requestId = 'n8_'.md5(uniqid());

            $this->oceanClickService->save([
                'request_id' => $requestId,
                'ad_id' => $item['adid'],
                'creative_id'=> $item['creativeId'],
                'channel_id'=> $channel['id'],
                'ip'        => $item['ip'],
                'ua'        => $item['ua'],
                'click_at'  => $actionTime,
                'callback_param'  => is_null($item['callback']) ? '' : $item['callback']
            ]);
        }


        return [
            'open_id'       => $openId,
            'action_time'   => $actionTime,
            'type'          => $this->getType(),
            'cp_channel_id' => $item['promotionId'],
            'request_id'    => $requestId,
            'ip'            => $item['ip'],
            'action_id'     => $openId,
            'extend'        => $this->filterExtendInfo($item),
            'data'          => $item
        ];
    }


}
