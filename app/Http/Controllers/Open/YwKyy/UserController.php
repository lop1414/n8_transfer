<?php


namespace App\Http\Controllers\Open\YwKyy;


use App\Common\Enums\CpTypeEnums;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
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
     * @throws CustomException
     * 分配行为
     */
    public function distribute(Request $request){
        try {


            $requestData = $request->all();
            $type = $requestData['type'];

            if ($type == 'REGISTER') {

                $this->reg($requestData);
            } elseif ($type == 'ADD_DESKTOP') {

                $this->addShortcut($requestData);
            } else {
                throw new CustomException([
                    'code' => 'UNKNOWN_TYPE',
                    'message' => '未知类型:' . $type,
                    'log' => true,
                    'data' => $requestData,
                ]);
            }
            return $this->_response(0, 'success');
        }catch (\Exception $e){

            //日志
            (new ErrorLogService())->catch($e);
            return $this->_response($e->getCode(), 'fail:'.$e->getMessage());
        }

    }


    /**
     * @param $requestData
     * @return mixed
     * 注册
     */
    public function reg($requestData){
        $rawData = $requestData;

        if(isset($requestData['ua']) && !empty($requestData['ua'])){
            $requestData['ua'] = base64_decode($requestData['ua']);
        }
        $data = array_merge([
            'cp_type'     => CpTypeEnums::YW,
            'cp_product_alias' => $requestData['appflag'],
            'open_id'      => $requestData['guid'],
            'action_time'  => date('Y-m-d H:i:s',$requestData['time']),
            'type'         => UserActionTypeEnum::REG,
            'cp_channel_id'=> $requestData['channel_id'] ?? '',
            'request_id'   => $requestData['request_id'] ?? '',
            'ip'           => $requestData['ip'] ?? '',
            'extend'       => $this->filterDeviceInfo($requestData),
            'data'         => $rawData,
            'action_id'    => $requestData['guid'],
            'source'       => DataSourceEnums::CP
        ]);

        $service = new DataToQueueService(QueueEnums::USER_REG_ACTION);
        $service->push($data);

        return $this->success();
    }




    /**
     * @param $requestData
     * @return mixed
     * 加桌行为
     */
    public function addShortcut($requestData){
        $rawData = $requestData;

        if(isset($requestData['ua']) && !empty($requestData['ua'])){
            $requestData['ua'] = base64_decode($requestData['ua']);
        }
        $data = array_merge([
            'cp_type'     => CpTypeEnums::YW,
            'cp_product_alias' => $requestData['appflag'],
            'open_id'      => $requestData['guid'],
            'action_time'  => date('Y-m-d H:i:s',$requestData['time']),
            'type'         => UserActionTypeEnum::ADD_SHORTCUT,
            'cp_channel_id'=> $requestData['channel_id'] ?? '',
            'request_id'   => $requestData['request_id'] ?? '',
            'ip'           => $requestData['ip'] ?? '',
            'extend'       => $this->filterDeviceInfo($requestData),
            'data'         => $rawData,
            'action_id'    => $requestData['guid'],
            'source'       => DataSourceEnums::CP
        ]);

        $service = new DataToQueueService(QueueEnums::USER_ADD_SHORTCUT_ACTION);
        $service->push($data);
        return $this->success();
    }







}
