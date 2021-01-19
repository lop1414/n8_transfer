<?php


namespace App\Enums;


class ProductTypeEnums
{
    const KYY = 'KYY';
    const GZH = 'GZH';

    /**
     * @var string
     * 名称
     */
    static public $name = '产品类型';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::KYY, 'name' => '快应用'],
        ['id' => self::GZH, 'name' => '公众号'],
    ];

}
