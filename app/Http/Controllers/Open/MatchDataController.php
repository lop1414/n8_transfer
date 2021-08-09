<?php

/**
 * 二版匹配数据处理
 */
namespace App\Http\Controllers\Open;


use App\Common\Enums\AdvAliasEnum;
use App\Common\Services\DataToQueueService;
use App\Common\Services\ErrorLogService;
use App\Enums\QueueEnums;
use App\Enums\UserActionTypeEnum;
use Illuminate\Http\Request;

class MatchDataController extends BaseController
{


    /**
     * @param Request $request
     * @return mixed
     * 头条
     */
    public function secondVersion(Request $request){
        try {
            $advMap = [
                'JRTT'  => AdvAliasEnum::OCEAN,
                'BAIDU' => AdvAliasEnum::BD,
                'KUAISHOU' => AdvAliasEnum::KS,
            ];
            $requestData = $request->all();

            $data['data']   = $requestData['extend'] ?? [];
            $data['data']['ua']   = $requestData['ua'] ?? '';
            $data['data']['ip']   = $requestData['ip'] ?? '';
            $data['data']['link_info'] = $this->get_link_para($requestData['extend']['link'] ?? '');

            $data['adv_alias'] = $advMap[$requestData['adv_alias']];
            $data['cp_type'] = $requestData['cp_type'];
            $data['cp_product_alias'] = $requestData['cp_product_alias'];
            $data['open_id'] = $requestData['open_id'];
            $data['cp_channel_id'] = $requestData['cp_channel_id'] ?? '';

            // 转化行为
            $data['type'] = UserActionTypeEnum::SECOND_VERSION_REG;
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
