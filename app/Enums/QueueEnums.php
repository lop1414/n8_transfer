<?php


namespace App\Enums;

/**
 * 队列枚举
 * Class QueueEnums
 * @package App\Enums
 */
class QueueEnums
{

    const USER_REG_ACTION = 'USER_REG_ACTION';
    const USER_ORDER_ACTION = 'USER_ORDER_ACTION';
    const USER_LOGIN_ACTION = 'USER_LOGIN_ACTION';
    const USER_ADD_SHORTCUT_ACTION = 'USER_ADD_SHORTCUT_ACTION';
    const USER_FOLLOW_ACTION = 'USER_FOLLOW_ACTION';
    const USER_READ_ACTION = 'USER_READ_ACTION';
    const USER_COMPLETE_ORDER_ACTION = 'USER_COMPLETE_ORDER_ACTION';
    const MATCH_DATA = 'MATCH_DATA';




    /**
     * @var string
     * 名称
     */
    static public $name = '队列枚举';


    static public $list = [
        ['id' => self::USER_REG_ACTION,        'name' => '注册行为'],
        ['id' => self::USER_ORDER_ACTION,      'name' => '下单行为'],
        ['id' => self::USER_LOGIN_ACTION,      'name' => '登陆行为'],
        ['id' => self::USER_ADD_SHORTCUT_ACTION,'name' => '加桌行为'],
        ['id' => self::USER_FOLLOW_ACTION,      'name' => '关注行为'],
        ['id' => self::USER_READ_ACTION,        'name' => '阅读行为'],
        ['id' => self::USER_COMPLETE_ORDER_ACTION,'name' => '完成订单'],
        ['id' => self::MATCH_DATA,        'name' => '匹配数据']
    ];


}
