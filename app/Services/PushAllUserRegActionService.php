<?php

namespace App\Services;


use App\Common\Enums\ReportStatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Tools\CustomException;
use App\Enums\UserActionTypeEnum;
use App\Models\UserActionLogModel;

/**
 * Class PushAllUserRegActionService
 * @package App\Services
 * 兼容阅文数据 后期补充渠道 的问题
 */
class PushAllUserRegActionService extends BaseService
{


    /**
     * @var
     * 行为类型
     */
    protected $actionType = UserActionTypeEnum::REG;



    public function __construct(){
        parent::__construct();
        $this->model = new UserActionLogModel();
    }




    /**
     * @throws CustomException
     * 上报
     */
    public function push(){
        $monthList = Functions::getMonthListByRange(['2019-10-01',date('Y-m-d')],'Y-m-01 00:00:00');
        $pushUserActionService = (new PushUserActionService())
            ->setActionType(UserActionTypeEnum::REG);


        foreach ($monthList as $month){
            echo $month. "\n";
            do{
                $list =  $this->model
                    ->setTableNameWithMonth($month)
                    ->where('type',$this->actionType)
                    ->where('status',ReportStatusEnum::WAITING)
                    ->skip(0)
                    ->take(1000)
                    ->orderBy('action_time')
                    ->get();

                foreach ($list as $item){
                    $pushUserActionService
                        ->setProduct($item['product_id'])
                        ->pushItem($item);
                }
            }while(!$list->isEmpty());
        }
    }


}
