<?php

namespace App\Services\AdvClick;



use App\Common\Enums\AdvAliasEnum;
use App\Common\Enums\ReportStatusEnum;
use App\Common\Services\SystemApi\AdvOceanApiService;
use App\Models\OceanClickModel;

class OceanClickService extends AdvClickService
{

    protected $adv = AdvAliasEnum::OCEAN;

    protected $advOceanApiService;


    public function __construct(){
        parent::__construct();
        $this->model = new OceanClickModel();
        $this->advOceanApiService = new AdvOceanApiService();
    }


    public function save($data){
        $this->model->create([
            'click_source' => $this->clickSource,
            'campaign_id'  => $data['campaign_id'] ?? '',
            'ad_id'        => $data['ad_id'] ?? '',
            'creative_id'  => $data['creative_id'] ?? '',
            'request_id'   => $data['request_id'] ?? '',
            'channel_id'   => $data['channel_id'] ?? 0,
            'creative_type'=> $data['creative_type'] ?? '',
            'creative_site'=> $data['creative_site'] ?? '',
            'convert_id'   => $data['convert_id'] ?? '',
            'muid'         => $data['muid'] ?? '',
            'android_id'   => $data['android_id'] ?? '',
            'oaid'         => $data['oaid'] ?? '',
            'oaid_md5'     => $data['oaid_md5'] ?? '',
            'os'           => $data['os'] ?? '',
            'ip'           => $data['ip'] ?? '',
            'ua'           => mb_substr(($data['ua'] ?? ''),0,1024),
            'click_at'     => $data['click_at'] ?? '',
            'callback_param'   => $data['callback_param'] ?? '',
            'model'         => $data['model'] ?? '',
            'union_site'    => $data['union_site'] ?? '',
            'caid'          => $data['caid'] ?? '',
            'link'          => $data['link'] ?? '',
            'extends'       => $data,
            'status'        => ReportStatusEnum::WAITING
        ]);
    }


    public function pushItem($item){
        $tmp = $item->toArray();
        $tmp['click_at'] = strtotime($tmp['click_at']). '000';
        $this->advOceanApiService->apiCreateClick($tmp,$item['click_source']);
    }



}
