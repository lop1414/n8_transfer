<?php

namespace App\Services\AdvClick;



use App\Common\Enums\AdvAliasEnum;
use App\Common\Enums\ReportStatusEnum;
use App\Models\KuaiShouClickModel;

class KusiShouClickService extends AdvClickService
{

    protected $adv = AdvAliasEnum::KUAIS_HOU;


    public function __construct(){
        parent::__construct();
        $this->model = new KuaiShouClickModel();
    }


    public function save($data){
        $this->model->create([
            'click_source' => $this->clickSource,
            'click_at'     => $data['click_at'] ?? '',
            'channel_id'   => $data['channel_id'] ?? 0,
            'data' => $data,
            'status'  => ReportStatusEnum::WAITING
        ]);
    }




    public function pushItem($item){}

}
