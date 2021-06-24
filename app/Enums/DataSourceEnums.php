<?php


namespace App\Enums;

/**
 * 数据源枚举
 * Class QueueEnums
 * @package App\Enums
 */
class DataSourceEnums
{
    const CP = 'CP';
    const SECOND_VERSION = 'SECOND_VERSION';
    const CP_PULL = 'CP_PULL';




    /**
     * @var string
     * 名称
     */
    static public $name = '队列枚举';


    static public $list = [
        ['id' => self::CP,        'name' => '书城'],
        ['id' => self::CP_PULL,        'name' => '书城接口检测'],
        ['id' => self::SECOND_VERSION,  'name' => '二版'],
    ];


}
