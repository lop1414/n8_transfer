<?php

namespace App\Models;

use App\Common\Models\BaseModel;

class OceanClickModel extends BaseModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'ocean_clicks';



    protected $fillable = [
        'click_source',
        'campaign_id',
        'ad_id',
        'creative_id',
        'request_id',
        'channel_id',
        'creative_type',
        'creative_site',
        'convert_id',
        'muid',
        'android_id',
        'oaid',
        'oaid_md5',
        'os',
        'ip',
        'ua',
        'click_at',
        'callback_param',
        'model',
        'union_site',
        'caid',
        'link',
        'extends',
        'status'
    ];




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
