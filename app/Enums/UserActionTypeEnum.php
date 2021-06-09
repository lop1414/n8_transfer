<?php

namespace App\Enums;

class UserActionTypeEnum
{

    const REG = 'REG';
    const READ = 'READ';
    const ADD_SHORTCUT = 'ADD_SHORTCUT';
    const FOLLOW = 'FOLLOW';
    const LOGIN = 'LOGIN';
    const ORDER = 'ORDER';
    const COMPLETE_ORDER = 'COMPLETE_ORDER';
    const RETENT = 'RETENT';
    const FORM = 'FORM';
    const APP_PAY = 'APP_PAY';
    const SECOND_VERSION_REG = 'SECOND_VERSION_REG';

    /**
     * @var string
     * 名称
     */
    static public $name = '用户行为类型';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::REG,          'name' => '注册'],
        ['id' => self::READ,         'name' => '阅读'],
        ['id' => self::ADD_SHORTCUT, 'name' => '加桌'],
        ['id' => self::FOLLOW,       'name' => '关注'],
        ['id' => self::LOGIN,        'name' => '登陆'],
        ['id' => self::ORDER,        'name' => '下单'],
        ['id' => self::COMPLETE_ORDER,'name' => '完成订单'],
        ['id' => self::RETENT,        'name' => '次留'],
        ['id' => self::FORM,         'name' => '表单预约'],
        ['id' => self::APP_PAY,      'name' => 'APP内付费'],
        ['id' => self::SECOND_VERSION_REG,      'name' => '二版注册匹配上报'],
    ];
}
