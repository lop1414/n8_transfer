<?php

namespace App\Models;

use App\Common\Models\BaseModel;

class ConfigModel extends BaseModel
{

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;


    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'config';


    protected $fillable = [
        'group',
        'k',
        'v'
    ];



    /**
     * @param $value
     * @return array
     * 属性访问器
     */
    public function getVAttribute($value)
    {
        return json_decode($value,true);
    }

    /**
     * @param $value
     * 属性修饰器
     */
    public function setVAttribute($value)
    {
        $this->attributes['v'] = json_encode($value);
    }


}
