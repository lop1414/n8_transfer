<?php

namespace App\Services\AdvClick;



use App\Common\Enums\AdvAliasEnum;
use App\Common\Enums\ReportStatusEnum;
use App\Models\BaiDuClickModel;

class BaiduClickService extends AdvClickService
{

    protected $adv = AdvAliasEnum::BAI_DU;


    public function __construct(){
        parent::__construct();
        $this->model = new BaiDuClickModel();
    }


    public function save($data){
        $this->model->create([
            'click_source' => $this->clickSource,
            'click_at'     => $data['click_at'] ?? '',
            'channel_id'   => $data['channel_id'] ?? 0,
            'extends'       => $data,
            'status'        => ReportStatusEnum::WAITING
        ]);
    }




    public function pushItem($item){}

}
