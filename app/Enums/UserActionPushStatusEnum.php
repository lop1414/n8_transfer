<?php

namespace App\Enums;

class UserActionPushStatusEnum
{

    const WAITING = 'WAITING';
    const DONE = 'DONE';

    /**
     * @var string
     * 名称
     */
    static public $name = '用户行为数据推送状态';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::WAITING, 'name' => '待推送'],
        ['id' => self::DONE, 'name' => '推送完成']
    ];
}
