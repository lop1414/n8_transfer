<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Common\Helpers\Functions;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Enums\UserActionTypeEnum;
use App\Services\ProductService;
use App\Services\UserAction\UserActionInterface;
use App\Services\UserActionService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;

class SyncUserActionCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'sync_user_action {--action_type=} {--cp_type=} {--product_type=} {--time=} {--time_interval=} {--product_id=} {--key_suffix=}';


    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '拉取用户行为数据';



    /**
     * @var
     * 时间间隔
     */
    protected $timeInterval = 60*10;



    public function handle(){
        $actionType = $this->option('action_type');
        $actionType && Functions::hasEnum(UserActionTypeEnum::class, $actionType);

        $cpType = $this->option('cp_type');
        $cpType && Functions::hasEnum(CpTypeEnums::class, $cpType);

        $productType = $this->option('product_type');
        $productType && Functions::hasEnum(ProductTypeEnums::class, $productType);

        $lockKey = "sync|{$cpType}|{$productType}|{$actionType}";
        // key 后缀
        $keySuffix = $this->option('key_suffix');
        if(!empty($keySuffix)){
            $lockKey .= '_'. trim($keySuffix);
        }

        $this->lockRun(function () use ($cpType,$productType,$actionType){
            $this->action($cpType,$productType,$actionType);
        },$lockKey,60*60*1,['log' => true]);


    }



    public function action($cpType,$productType,$actionType){
        $startRunTime = microtime(true);

        // 时间参数
        $time = $this->option('time');
        list($startTime,$endTime) = explode(",", $time);
        $endTime = min($endTime,date('Y-m-d H:i:s'));
        Functions::checkTimeRange($startTime,$endTime);

        // 时间区间
        $timeInterval = $this->option('time_interval') ?? $this->timeInterval;

        $productId = $this->option('product_id');
        if(!empty($productId)){
            $productService = new ProductService();
            $products = $productService->get(['id' => $productId]);
            $product = $products[0];
            $cpType = $product['cp_type'];
            $productType = $product['type'];
        }

        $container = Container::getInstance();
        $services = UserActionService::getServices();

        foreach ($services as $service){
            try{
                $container->bind(UserActionInterface::class,$service);
                $userActionService = $container->make(UserActionService::class);

                if(!empty($cpType) &&  $cpType != $userActionService->getCpType()){
                    continue;
                }

                if(!empty($actionType) &&  $actionType != $userActionService->getType()){
                    continue;
                }

                if(!empty($productType) &&  $productType != $userActionService->getProductType()){
                    continue;
                }

                !empty($productId) && $userActionService->setParam('product_id',$productId);

                echo $userActionService->getCpType().':'.$userActionService->getProductType().':'.$userActionService->getType()."\n";

                $tmpStartTime = $startTime;
                while($tmpStartTime < $endTime){
                    $tmpEndTime = date('Y-m-d H:i:s',  strtotime($tmpStartTime) + $timeInterval);
                    $tmpEndTime = min($tmpEndTime,$endTime);

                    // 跨月
                    if(intval(date('m',strtotime($tmpEndTime))) - intval(date('m',strtotime($tmpStartTime))) > 0){
                        $tmpStartMonth = date('Y-m',strtotime($tmpStartTime));

                        //开始时间月份最后一天
                        $tmpEndTime = date('Y-m-d',strtotime("{$tmpStartMonth} +1 month -1 day")) . " 23:59:59";
                        echo "时间 : {$tmpStartTime} ~ {$tmpEndTime}\n";

                        $userActionService->setParam('start_time',$tmpStartTime);
                        $userActionService->setParam('end_time',$tmpEndTime);

                        // 开始时间的下个月第一天
                        $tmpStartTime = date('Y-m',strtotime("{$tmpStartMonth} +1 month")) . "-01 00:00:00";

                    }else{
                        echo "时间 : {$tmpStartTime} ~ {$tmpEndTime}\n";

                        $userActionService->setParam('start_time',$tmpStartTime);
                        $userActionService->setParam('end_time',$tmpEndTime);
                        $tmpStartTime = $tmpEndTime;
                    }

//                    $userActionService->sync();

                    // 避免频率限制（FQ）
                    sleep(1);
                }
            }catch (CustomException $e){
                //日志
                (new ErrorLogService())->catch($e);

                continue;
            } catch (\Exception $e){

                //日志
                (new ErrorLogService())->catch($e);
               continue;
            }
        }

        $endRunTime = microtime(true);
        echo "\n用时".($endRunTime - $startRunTime)."秒\n";
    }
}
