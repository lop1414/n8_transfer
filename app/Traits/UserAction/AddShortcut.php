<?php

namespace App\Traits\UserAction;

use App\Common\Tools\CustomRedis;
use App\Enums\UserActionTypeEnum;

trait AddShortcut
{

    public function getType(): string
    {
        return UserActionTypeEnum::ADD_SHORTCUT;
    }


    /**
     * @param $data
     * @return bool
     * 是否重复加桌
     */
    public function isRepeatAddShortcut($data): bool
    {
        $key = $this->getAddShortcutLogKey($data);
        $customRedis = new CustomRedis();
        $info = $customRedis->get($key);
        return !!$info;
    }


    /**
     * @param $data
     * @return bool
     * 设置加桌缓存记录
     */
    public function setAddShortcutCacheLog($data): bool
    {
        $key = $this->getAddShortcutLogKey($data);
        $customRedis = new CustomRedis();
        $customRedis->set($key,1);
        $customRedis->expire($key,7200);
        return true;
    }

    /**
     * @param $data
     * @return string
     * 后期缓存下标
     */
    public function getAddShortcutLogKey($data): string
    {
        $keyArr = ['user_add_shortcut_log',$data['product_id'],$data['open_id'],$data['cp_channel_id'],$data['action_id']];
        return implode(':',$keyArr);
    }
}
