<?php

namespace App\Services;

use App\Common\Services\BaseService;
use App\Common\Services\SystemApi\UnionApiService;
use App\Common\Tools\CustomRedis;


class ChannelService extends BaseService
{
    /**
     * @var CustomRedis
     */
    protected $customRedis;

    public function getCustomRedis(): CustomRedis
    {
        if(empty($this->customRedis)){
            $this->customRedis = new CustomRedis();
        }
        return $this->customRedis;
    }



    public function readChannelByCpChannelId($cpType,$cpChannelId): array
    {
        $customRedis = $this->getCustomRedis();
        $key = 'channel:'.$cpType.':'.$cpChannelId;
        $info = $customRedis->get($key);

        $ttl = 60*60*24*7;
        if($info === false){
            $channels = (new UnionApiService())->apiGetChannel(['cp_channel_id' => $cpChannelId]);
            foreach ($channels as $channel){
                // 设置缓存
                $ret = $customRedis->set($key, $channel);
                if($ttl > 0){
                    $customRedis->expire($key, $ttl);
                }
            }
            $info = $customRedis->get($key);
        }

        return empty($info) ? [] : $info;
    }



}
