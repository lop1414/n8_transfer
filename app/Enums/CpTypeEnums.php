<?php


namespace App\Enums;


class CpTypeEnums
{
    const TW = 'TW';
    const YW = 'YW';
    const BM = 'BM';

    /**
     * @var string
     * 名称
     */
    static public $name = '产品平台类型';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::TW, 'name' => '腾文'],
        ['id' => self::YW, 'name' => '阅文'],
        ['id' => self::BM, 'name' => '笔墨'],
    ];

}
