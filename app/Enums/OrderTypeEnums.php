<?php


namespace App\Enums;


class OrderTypeEnums
{
    const NORMAL = 'NORMAL';
    const ANNUAL = 'ANNUAL';
    const ACTIVITY = 'ACTIVITY';
    const OTHER = 'OTHER';

    /**
     * @var string
     * 名称
     */
    static public $name = '充值类型';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::NORMAL, 'name' => '普通充值'],
        ['id' => self::ANNUAL, 'name' => '年费充值'],
        ['id' => self::ACTIVITY, 'name' => '活动充值'],
        ['id' => self::OTHER, 'name' => '其它'],
    ];

}
