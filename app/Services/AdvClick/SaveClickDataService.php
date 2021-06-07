<?php

namespace App\Services\AdvClick;

use App\Common\Enums\AdvAliasEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;

use App\Common\Tools\CustomException;

class SaveClickDataService extends BaseService
{


    protected $model;


    protected $clickService;



    public function __construct(){
        parent::__construct();
    }



    /**
     * @param $adv
     * @param $data
     * @throws CustomException
     * 保存广告点击数据
     */
    public function saveAdvClickData($adv,$data){
        $service = $this->getClickService($adv);
        $service->save($data);
    }



    /**
     * @param $adv
     * @return mixed
     * @throws CustomException
     * 分发各广告商ClickService
     */
    public function getClickService($adv){
        if(empty($this->clickService[$adv])){
            Functions::hasEnum(AdvAliasEnum::class,$adv);

            $action = ucfirst(Functions::camelize($adv));
            $class = "App\Services\AdvClick\\{$action}ClickService";

            if(!class_exists($class)){
                throw new CustomException([
                    'code' => 'NOT_FOUND_CLASS',
                    'message' => "未知的类:{$class}",
                ]);
            }

            $this->clickService[$adv] = new $class;
        }

        return $this->clickService[$adv];
    }






}
