<?php

namespace App\Models;

use App\Common\Models\BaseModel;

class MatchDataModel extends BaseModel
{
    protected $fillable = [
        'product_id',
        'open_id',
        'cp_channel_id',
        'adv_alias',
        'type',
        'data',
    ];



    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'match_data';





    /**
     * @param $value
     * @return array
     * 属性访问器
     */
    public function getDataAttribute($value)
    {
        return json_decode($value,true);
    }

    /**
     * @param $value
     * 属性修饰器
     */
    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
    }

}
