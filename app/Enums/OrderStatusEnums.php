<?php


namespace App\Enums;


class OrderStatusEnums
{
    const UN_PAID = 'UN_PAID';
    const CANCEL  = 'CANCEL';
    const COMPLETE = 'COMPLETE';

    /**
     * @var string
     * 名称
     */
    static public $name = '订单状态';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::UN_PAID, 'name' => '待支付'],
        ['id' => self::CANCEL, 'name' => '取消'],
        ['id' => self::COMPLETE, 'name' => '完成'],
    ];

}
