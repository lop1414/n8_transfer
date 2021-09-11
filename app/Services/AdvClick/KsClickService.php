<?php

namespace App\Services\AdvClick;



use App\Common\Enums\AdvAliasEnum;
use App\Common\Enums\ReportStatusEnum;
use App\Common\Services\SystemApi\AdvKsApiService;
use App\Models\KuaiShouClickModel;

class KsClickService extends AdvClickService
{

    protected $adv = AdvAliasEnum::KS;

    protected $advKsApiService;


    public function __construct(){
        parent::__construct();
        $this->model = new KuaiShouClickModel();
        $this->advKsApiService = new AdvKsApiService();
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

        if(isset($extends['raw_data'])){
            $rawData = $extends['raw_data'] ?? [];
            $data = [
                'campaign_id' => $rawData['campaign_id'] ?? '',
                'unit_id'     => $rawData['aid'] ?? '',
                'creative_id' => $rawData['cid'] ?? '',
                'channel_id'  => $tmp['channel_id'],
                'request_id'  => $tmp['request_id'],
                'muid'        => $rawData['muid'] ?? '',
                'android_id'  => $rawData['android_id'] ?? '',
                'oaid'        => $rawData['oaid'] ?? '',
                'os'          => $rawData['os'] ?? '',
                'oaid_md5'    => $rawData['oaid_md5'] ?? '',
                'ip'          => $rawData['ip'] ?? '',
                'ua'          => $rawData['ua'] ?? '',
                'click_at'    => strtotime($tmp['click_at']). '000',
                'callback'    => $extends['url_info']['callback'] ?? '',
            ];
        }else{
            $unitId = $extends['ad_id'] ?? '';
            if(empty($unitId)){
                $unitId = $extends['aid'] ?? '';
            }

            $creativeId = $extends['creative_id'] ?? '';
            if(empty($creativeId)){
                $creativeId = $extends['cid'] ?? '';
            }


            $data = [
                'campaign_id' => '',
                'unit_id'     => $unitId,
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
                'callback'    => $extends['url_info']['callback'] ?? '',
            ];
        }




        $this->advKsApiService->apiCreateClick($data,$item['click_source']);
    }

}
