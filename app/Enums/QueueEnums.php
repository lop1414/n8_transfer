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
    const OCEAN_MATCH_DATA = 'OCEAN_MATCH_DATA';
    const KUAI_SHOU_MATCH_DATA = 'KUAI_SHOU_MATCH_DATA';
    const FORWARD_DATA = 'FORWARD_DATA';



    /**
     * @var string
     * 名称
     */
    static public $name = '队列枚举';


    static public $list = [
        ['id' => self::USER_REG_ACTION,         'name' => '注册行为', 'type' => 'action'],
        ['id' => self::USER_ORDER_ACTION,       'name' => '下单行为', 'type' => ''],
        ['id' => self::USER_LOGIN_ACTION,       'name' => '登陆行为', 'type' => ''],
        ['id' => self::USER_ADD_SHORTCUT_ACTION,'name' => '加桌行为', 'type' => 'action'],
        ['id' => self::USER_FOLLOW_ACTION,      'name' => '关注行为', 'type' => ''],
        ['id' => self::USER_READ_ACTION,        'name' => '阅读行为', 'type' => ''],
        ['id' => self::USER_COMPLETE_ORDER_ACTION,'name' => '完成订单', 'type' => ''],
        ['id' => self::OCEAN_MATCH_DATA,        'name' => '头条匹配数据', 'type' => 'match'],
        ['id' => self::KUAI_SHOU_MATCH_DATA,    'name' => '快手匹配数据', 'type' => 'match'],
        ['id' => self::FORWARD_DATA,    'name' => '转发数据', 'type' => '']
    ];


}
