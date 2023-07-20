<?php


namespace App\Http\Controllers\Open\YwdjWechatMiniProgram;


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

    protected $cpType = CpTypeEnums::YWDJ;

    /**
     * 注册
     * @param Request $request
     * @return mixed
     */
    public function reg(Request $request){
        try {
            $requestData = $request->all();

            $ua = base64_decode($requestData['ua'] ?? '');
            $data = array_merge([
                'cp_type'     => $this->cpType,
                'cp_product_alias' => $requestData['appflag'],
                'open_id'      => $requestData['guid'],
                'action_time'  => date('Y-m-d H:i:s',$requestData['time']),
                'type'         => UserActionTypeEnum::REG,
                'cp_channel_id'=> $requestData['channel_id'],
                'request_id'   => '',
                'ip'           => $requestData['ip'],
                'extend'       => array_merge($this->filterDeviceInfo($requestData),['ua' => $ua]),
                'data'         => $requestData,
                'action_id'    => $requestData['guid'],
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
