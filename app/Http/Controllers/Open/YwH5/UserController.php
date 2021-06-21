<?php


namespace App\Http\Controllers\Open\YwH5;


use App\Common\Enums\CpTypeEnums;
use App\Common\Services\ErrorLogService;
use App\Enums\DataSourceEnums;
use App\Enums\QueueEnums;
use App\Common\Services\DataToQueueService;
use App\Enums\UserActionTypeEnum;
use App\Http\Controllers\Open\BaseController;
use Illuminate\Http\Request;

class UserController extends BaseController
{


    /**
     * @param Request $request
     * @return mixed
     */
    public function reg(Request $request){
        try {
            $requestData = $request->all();
            $rawData = $requestData;

            if(isset($requestData['ua']) && !empty($requestData['ua'])){
                $requestData['ua'] = base64_decode($requestData['ua']);
            }
            $data = array_merge([
                'cp_type'     => CpTypeEnums::YW,
                'cp_product_alias' => $requestData['appflag'],
                'open_id'      => $requestData['open_id'],
                'action_time'  => date('Y-m-d H:i:s',$requestData['time']),
                'type'         => UserActionTypeEnum::REG,
                'cp_channel_id'=> $requestData['channel_id'] ?? '',
                'request_id'   => $requestData['request_id'] ?? '',
                'ip'           => $requestData['ip'] ?? '',
                'extend'       => $this->filterDeviceInfo($requestData),
                'data'         => $rawData,
                'action_id'    => $requestData['open_id'],
                'source'       => DataSourceEnums::CP
            ]);

            $service = new DataToQueueService(QueueEnums::USER_REG_ACTION);
            $service->push($data);

            return $this->success();
        }catch (\Exception $e){

            //日志
            (new ErrorLogService())->catch($e);
            return $this->_response($e->getCode(), 'fail:'.$e->getMessage());
        }

    }
}
