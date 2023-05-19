<?php


namespace App\Http\Controllers\Open\YgWechatMiniProgram;


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

    protected $cpType = CpTypeEnums::YG;

    /**
     * 注册
     * @param Request $request
     * @return mixed
     */
    public function reg(Request $request){

        try {
            $requestData = $request->all();

            $ua = $requestData['userAgent'] ?? '';
            $data = array_merge([
                'cp_type'     => $this->cpType,
                'cp_product_alias' => $requestData['channleCode'],
                'open_id'      => $requestData['userId'],
                'action_time'  => $requestData['regTime'],
                'type'         => UserActionTypeEnum::REG,
                'cp_channel_id'=> $requestData['referralId'],
                'request_id'   => '',
                'ip'           => $requestData['ip'],
                'extend'       => array_merge($this->filterDeviceInfo($requestData),['ua' => $ua]),
                'data'         => $requestData,
                'action_id'    => $requestData['userId'],
                'source'       => DataSourceEnums::CP
            ]);

            $service = new DataToQueueService(QueueEnums::USER_REG_ACTION);
            $service->push($data);

            return $this->_response(0, 'success');
        }catch (\Exception $e){

            //日志
            (new ErrorLogService())->catch($e);
            return $this->_response($e->getCode(), 'fail:'.$e->getMessage());
        }
    }

}
