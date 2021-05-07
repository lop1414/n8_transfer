<?php

namespace App\Services\AdvClick;



use App\Common\Enums\AdvAliasEnum;
use App\Common\Enums\ReportStatusEnum;
use App\Models\GdtClickModel;

class GdtClickService extends AdvClickService
{

    protected $adv = AdvAliasEnum::GDT;


    public function __construct(){
        parent::__construct();
        $this->model = new GdtClickModel();
    }


    public function save($data){
        $this->model->create([
            'click_source' => $this->clickSource,
            'click_at'     => $data['click_at'] ?? '',
            'request_id'   => $data['request_id'] ?? '',
            'channel_id'   => $data['channel_id'] ?? '',
            'extends' => $data,
            'status'  => ReportStatusEnum::WAITING
        ]);
    }





    /**
     * 预处理 还没有广电通广告系统
     */
    public function pushPrepare(){}


    public function pushItem($item){}

}
