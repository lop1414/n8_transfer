<?php

/**
 * 转发上报数据处理
 */
namespace App\Http\Controllers\Open\YwKyy;


use App\Common\Enums\AdvAliasEnum;
use App\Common\Enums\CpTypeEnums;
use App\Common\Services\DataToQueueService;
use App\Common\Services\ErrorLogService;
use App\Enums\QueueEnums;
use App\Enums\UserActionTypeEnum;
use App\Http\Controllers\Open\BaseController;
use Illuminate\Http\Request;

class MatchDataController extends BaseController
{


    /**
     * @param Request $request
     * @return mixed
     * 头条
     */
    public function ocean(Request $request){
        try {
            $requestData = $request->all();
            $data['data']['raw_data'] = $requestData;
            $data['cp_type'] = CpTypeEnums::YW;
            $data['adv_alias'] = AdvAliasEnum::OCEAN;
            $data['cp_product_alias'] = $requestData['appflag'];
            $data['open_id'] = $requestData['guid'];
            $data['cp_channel_id'] = $requestData['channel_id'] ?? '';
            $url = urldecode(base64_decode(urldecode($requestData['url'])));
            $urlInfo = $this->get_link_para($url);

            // 转化行为
            $convertType = $urlInfo['event_type'] ?? '';
            $map = [
                0 => UserActionTypeEnum::REG,
                1 => UserActionTypeEnum::ADD_SHORTCUT,
                2 => UserActionTypeEnum::COMPLETE_ORDER,
                3 => UserActionTypeEnum::FORM,
                6 => UserActionTypeEnum::RETENT
            ];

            $data['type'] = $map[$convertType] ?? '';
            $data['data']['decode_url'] = $url;
            $data['data']['url_info'] = $urlInfo;
            $service = new DataToQueueService(QueueEnums::OCEAN_MATCH_DATA);
            $service->push($data);
            return $this->_response(0,'success');

        }catch (\Exception $e){

            //日志
            (new ErrorLogService())->catch($e);
            return $this->_response($e->getCode(), 'fail:'.$e->getMessage());
        }

    }



    /**
     * @param Request $request
     * @return mixed
     * 快手
     */
    public function kuaishou(Request $request){
        try {
            $requestData = $request->all();
            $data['data']['raw_data'] = $requestData;
            $data['cp_type'] = CpTypeEnums::YW;
            $data['adv_alias'] = AdvAliasEnum::KUAI_SHOU;
            $data['cp_product_alias'] = $requestData['appflag'];
            $data['open_id'] = $requestData['guid'];
            $data['cp_channel_id'] = $requestData['channel_id'] ?? '';
            $url = urldecode(base64_decode(urldecode($requestData['url'])));
            $urlInfo = $this->get_link_para($url);
            // 转化行为
            $convertType = $urlInfo['event_type'] ?? '';
            $map = [
                1 => UserActionTypeEnum::REG,
                2 => UserActionTypeEnum::ADD_SHORTCUT,
                3 => UserActionTypeEnum::COMPLETE_ORDER,
                7 => UserActionTypeEnum::RETENT
            ];
            $data['type'] = $map[$convertType] ?? '';
            $data['data']['decode_url'] = $url;
            $data['data']['url_info'] = $urlInfo;
            $service = new DataToQueueService(QueueEnums::KUAI_SHOU_MATCH_DATA);
            $service->push($data);

            return $this->_response(0,'success');
        }catch (\Exception $e){

            //日志
            (new ErrorLogService())->catch($e);
            return $this->_response($e->getCode(), 'fail:'.$e->getMessage());
        }
    }



    /**
     * 获取链接中的参数
     * @param $link
     * @return mixed
     */
    protected function get_link_para($link){
        $link_para = parse_url($link);
        $parameter = [];
        if(isset($link_para['query'])){
            parse_str($link_para['query'],  $parameter);
        }

        return $parameter;
    }

}
