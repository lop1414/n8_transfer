<?php

/**
 * 转发上报数据处理
 */
namespace App\Http\Controllers\Open\YwKyy;


use App\Common\Enums\AdvAliasEnum;
use App\Common\Services\DataToQueueService;
use App\Enums\QueueEnums;
use App\Enums\UserActionTypeEnum;
use App\Http\Controllers\Open\BaseController;
use Illuminate\Http\Request;

class MatchDataController extends BaseController
{

    /**
     * @param Request $request
     * 百度
     */
    public function ocean(Request $request){
        $requestData = $request->all();
        $requestData['adv_alias'] = AdvAliasEnum::OCEAN;
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
        $requestData['type'] = $map[$convertType] ?? '';
        if($requestData['type'] == UserActionTypeEnum::REG){
            // 异步 修改 user_action_log表 生成点击数据
            $service = new DataToQueueService(QueueEnums::USER_REG_ACTION_MATCH_HANDLE);
            $service->push($requestData);
        }

        $service = new DataToQueueService(QueueEnums::USER_ACTION);
        $service->push($requestData);

    }


    /**
     * @param Request $request
     * 快手
     */
    public function kuaishou(Request $request){
        $requestData = $request->all();
        $requestData['adv_alias'] = AdvAliasEnum::KUAISHOU;
        // 转化行为
        $convertType = $urlInfo['event_type'] ?? '';
        $map = [
            1 => UserActionTypeEnum::REG,
            2 => UserActionTypeEnum::ADD_SHORTCUT,
            3 => UserActionTypeEnum::COMPLETE_ORDER,
            7 => UserActionTypeEnum::RETENT
        ];
        $requestData['type'] = $map[$convertType] ?? '';

        if($requestData['type'] == UserActionTypeEnum::REG){
            // 异步 修改 user_action_log表 生成点击数据
            $service = new DataToQueueService(QueueEnums::USER_REG_ACTION_MATCH_HANDLE);
            $service->push($requestData);
        }

        $service = new DataToQueueService(QueueEnums::USER_ACTION);
        $service->push($requestData);

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
