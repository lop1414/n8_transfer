<?php

namespace App\Models;

use App\Common\Models\BaseModel;

class UserActionLogModel extends BaseModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'tmp_user_action_logs';



    protected $fillable = [
        'product_id',
        'open_id',
        'action_time',
        'type',
        'cp_channel_id',
        'request_id',
        'ip',
        'data',
        'status',
        'action_id',
        'matcher',
        'source'
    ];


    public function setTableNameWithMonth($dateTime){

        $name =  'user_action_logs_'. date('Ym',strtotime($dateTime));
        $this->table = $name;
        return $this;
    }



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



    /**
     * @param $value
     * @return array
     * 属性访问器
     */
    public function getFailDataAttribute($value)
    {
        return json_decode($value,true);
    }

    /**
     * @param $value
     * 属性修饰器
     */
    public function setFailDataAttribute($value)
    {
        $this->attributes['fail_data'] = json_encode($value);
    }

}
