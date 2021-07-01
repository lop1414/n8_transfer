<?php

namespace App\Services;

use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Enums\UserActionTypeEnum;
use App\Models\MatchDataModel;

class UpdateUserActionLogService extends BaseService
{


    protected $model;


    protected $product;

    protected $pageSize = 1000;


    /**
     * @var
     * 时间区间
     */
    protected $startTime,$endTime;


    public function __construct(){
        parent::__construct();
        $this->model = new MatchDataModel();
    }


    public function setProduct($product){
        $this->product = $product;
        return $this;
    }


    /**
     * @param $startTime
     * @param $endTime
     * @throws CustomException
     * 设置时间区间
     */
    public function setTimeRange($startTime,$endTime){
        if(date('m',strtotime($startTime)) != date('m',strtotime($startTime))){
            throw new CustomException([
                'code' => 'DATE_TIME_ERROR',
                'message' => '月份不一致',
            ]);
        }
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }






    public function reg(){
        $page = 0;
        do{
            $list = $this->model
                ->whereIn('type',[UserActionTypeEnum::ADD_SHORTCUT,UserActionTypeEnum::SECOND_VERSION_REG])
                ->where('product_id',$this->product['id'])
                ->where('request_id','')
                ->whereBetween('created_at',[$this->startTime,$this->endTime])
                ->skip($page * $this->pageSize)
                ->take($this->pageSize)
                ->get();

            foreach ($list as $item){
                try{
                    $advAlias = lcfirst(Functions::camelize($item['adv_alias']));
                    $info = (new MatchDataService())->$advAlias($item);
                    if(!empty($info)){
                        $item->request_id = $info['request_id'];
                        $item->save();
                    }

                }catch(CustomException $e){
                    (new ErrorLogService())->catch($e);

                    //日志
                    $errorInfo = $e->getErrorInfo(true);
                    echo $errorInfo['message']. "\n";

                }catch (\Exception $e){

                    //未命中唯一索引
                    if($e->getCode() != 23000){
                        //日志
                        (new ErrorLogService())->catch($e);
                        echo $e->getMessage()."\n";
                    }else{
                        echo "  命中唯一索引 \n";
                    }

                }

            }

            $page += 1;
        }while(!$list->isEmpty());
    }






}
