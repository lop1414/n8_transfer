<?php

namespace App\Services\AdvClick;

use App\Common\Enums\AdvClickSourceEnum;
use App\Common\Enums\ReportStatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Tools\CustomException;


class AdvClickService extends BaseService
{

    protected $adv;



    /**
     * @var string
     * 广告点击来源
     */
    protected $clickSource = AdvClickSourceEnum::N8_TRANSFER;


    protected $pageSize = 1000;



    /**
     * @param $data
     * 保存点击数据
     */
    public function save($data){}


    public function pushItem($item){}


    public function push(){


        do{
            $list = $this->model
                ->where('status',ReportStatusEnum::WAITING)
                ->skip(0)
                ->take($this->pageSize)
                ->get();
            foreach ($list as $item){
                try{
                    $this->pushItem($item);
                    $item->status = ReportStatusEnum::DONE;

                }catch(CustomException $e){
                    $errorInfo = $e->getErrorInfo(true);

                    $item->fail_data = $errorInfo;
                    $item->status = ReportStatusEnum::FAIL;

                    echo $errorInfo['message']. "\n";

                }catch(\Exception $e){
                    $item->fail_data = [
                        'code'      => $e->getCode(),
                        'message'   => $e->getMessage()
                    ];
                    $item->status = ReportStatusEnum::FAIL;

                    echo $e->getMessage(). "\n";
                }
                $item->save();

            }
        }while(!$list->isEmpty());
    }


}
