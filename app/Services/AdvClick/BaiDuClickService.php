<?php

namespace App\Services\AdvClick;



use App\Common\Enums\AdvAliasEnum;
use App\Common\Enums\ReportStatusEnum;
use App\Common\Services\SystemApi\AdvBdApiService;
use App\Models\BaiDuClickModel;

class BaiDuClickService extends AdvClickService
{

    protected $adv = AdvAliasEnum::BAI_DU;

    protected $advBdApiService;


    public function __construct(){
        parent::__construct();
        $this->model = new BaiDuClickModel();
        $this->advBdApiService = new AdvBdApiService();

    }


    public function save($data){
        $this->model->create([
            'click_source' => $this->clickSource,
            'click_at'     => $data['click_at'] ?? '',
            'channel_id'   => $data['channel_id'] ?? 0,
            'request_id'   => $data['request_id'] ?? '',
            'extends'       => $data,
            'status'        => ReportStatusEnum::WAITING
        ]);
    }




    public function pushItem($item){
        $tmp = $item->toArray();
        $extends = $tmp['extends'];
        if(!empty($extends['extends'])){
            $extends = $extends['extends'];
        }
        $creativeId = $extends['creative_id'] ?? '';
        if(empty($creativeId) && !empty($extends['c_id'])){
            $creativeId =  $extends['c_id'];
        }

        $data = [
            'campaign_id' => '',
            'adgroup_id'  => $extends['ad_id'] ?? '',
            'creative_id' => $creativeId,
            'channel_id'  => $tmp['channel_id'],
            'request_id'  => $tmp['request_id'],
            'muid'        => $extends['muid'] ?? '',
            'android_id'  => $extends['android_id'] ?? '',
            'oaid'        => $extends['oaid'] ?? '',
            'os'          => $extends['os'] ?? '',
            'oaid_md5'    => $extends['oaid_md5'] ?? '',
            'ip'          => $extends['ip'] ?? '',
            'ua'          => $extends['ua'] ?? '',
            'click_at'    => strtotime($tmp['click_at']). '000',
            'callback_url'=> '',
            'model'       => '',
            'combid'      => '',
            'link'        => $extends['link'] ?? '',
        ];

        $this->advBdApiService->apiCreateClick($data,$item['click_source']);
    }

}
