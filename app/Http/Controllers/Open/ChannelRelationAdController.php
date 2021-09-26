<?php

/**
 * 渠道关联计划
 */
namespace App\Http\Controllers\Open;



use App\Common\Enums\AdvAliasEnum;
use App\Common\Services\DataToQueueService;
use App\Enums\QueueEnums;
use Illuminate\Http\Request;

class ChannelRelationAdController extends BaseController
{



    public function ocean(Request $request){
        $reqData = $request->all();

        $reqData['adv_alias'] = AdvAliasEnum::OCEAN;

        $service = new DataToQueueService(QueueEnums::PUSH_CHANNEL_AD);
        $service->push($reqData);
        return $this->_response(0,'success');
    }


    public function baidu(Request $request){
        $reqData = $request->all();

        $reqData['adv_alias'] = AdvAliasEnum::BD;

        $service = new DataToQueueService(QueueEnums::PUSH_CHANNEL_AD);
        $service->push($reqData);
        return $this->_response(0,'success');
    }


    public function ks(Request $request){
        $reqData = $request->all();

        $reqData['adv_alias'] = AdvAliasEnum::KS;

        $service = new DataToQueueService(QueueEnums::PUSH_CHANNEL_AD);
        $service->push($reqData);
        return $this->_response(0,'success');
    }


}
