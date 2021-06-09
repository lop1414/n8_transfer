<?php

namespace App\Services;

use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
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

        do{
            $list = $this->model
                ->where('type',UserActionTypeEnum::ADD_SHORTCUT)
                ->where('product_id',$this->product['id'])
                ->where('request_id','')
                ->whereBetween('created_at',[$this->startTime,$this->endTime])
                ->skip(0)
                ->take($this->pageSize)
                ->get();

            foreach ($list as $item){
                //分发到各自service处理
                $class = $this->getService();
                $advAlias = lcfirst(Functions::camelize($item['adv_alias']));
                $info = (new $class)->$advAlias($item);
                if(!empty($info)){
                    $item->request_id = $info['request_id'];
                    $item->save();
                }
            }

        }while(!$list->isEmpty());
    }



    public function getService(){
        $cpType = ucfirst(Functions::camelize($this->product['cp_type']));
        $productType = ucfirst(Functions::camelize($this->product['type']));
        $class = "App\\Services\\{$cpType}{$productType}\\MatchDataService";
        if(!class_exists($class)){
            throw new CustomException([
                'code' => 'UNKNOWN_CLASS',
                'message' => '未知类',
                'log' => true,
                'data' => "{$class} 类不存在",
            ]);
        }
        return $class;
    }




}
