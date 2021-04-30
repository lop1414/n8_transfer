<?php

namespace App\Models;

use App\Common\Models\BaseModel;

class GdtClickModel extends BaseModel
{
    protected $fillable = [
        'click_source',
        'click_at',
        'request_id',
        'channel_id',
        'extends',
        'status'
    ];



    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'gdt_clicks';




    /**
     * @param $value
     * @return array
     * 属性访问器
     */
    public function getExtendsAttribute($value)
    {
        return json_decode($value,true);
    }

    /**
     * @param $value
     * 属性修饰器
     */
    public function setExtendsAttribute($value)
    {
        $this->attributes['extends'] = json_encode($value);
    }

}
