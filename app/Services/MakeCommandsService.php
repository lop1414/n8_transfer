<?php

namespace App\Services;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Enums\QueueEnums;
use App\Enums\UserActionTypeEnum;

class MakeCommandsService
{

    protected $userActionMap = [
        ProductTypeEnums::KYY => [
            UserActionTypeEnum::REG,
            UserActionTypeEnum::READ,
            UserActionTypeEnum::ADD_SHORTCUT,
//            UserActionTypeEnum::LOGIN,
            UserActionTypeEnum::ORDER,
            UserActionTypeEnum::COMPLETE_ORDER
        ],
        ProductTypeEnums::H5 => [
            UserActionTypeEnum::REG,
            UserActionTypeEnum::READ,
            UserActionTypeEnum::FOLLOW,
//            UserActionTypeEnum::LOGIN,
            UserActionTypeEnum::ORDER,
            UserActionTypeEnum::COMPLETE_ORDER
        ]
    ];


    public function make(){
        $path = base_path(). '/app/Services/';
        $content = file_get_contents($path.'TmpCommandsService.php');

        $content = str_replace('TmpCommandsService','CommandsService',$content);

        $str = $this->userActionQueueDataToDb();
        $content = str_replace('#commands|user_action_queue_data_to_db#',$str,$content);

        $str = $this->matchQueueDataToDb();
        $content = str_replace('#commands|match_queue_data_to_db#',$str,$content);

        $str = $this->pullUserAction();
        $content = str_replace('#commands|pull_user_action#',$str,$content);

        $str = $this->pushUserAction();
        $content = str_replace('#commands|push_user_action#',$str,$content);

        $fileName = $path.'CommandsService.php';
        file_put_contents($fileName,$content);
    }



    protected function userActionQueueDataToDb(){
        $list = QueueEnums::$list;
        $str = "";

        foreach ($list as $item){
            if($item['type'] != 'action') continue;

            $str .= "        //{$item['name']}\n";
            $tmpCommand = "user_action_data_to_db ";
            $tmpCommand .= "--enum={$item['id']} ";
            $str .= $this->echoCommand($tmpCommand);
        }
        $str .= "\n";
        return $str;
    }



    protected function matchQueueDataToDb(){
        $list = QueueEnums::$list;
        $str = "";

        foreach ($list as $item){
            if($item['type'] != 'match') continue;


            $str .= "        //{$item['name']}\n";
            $tmpCommand = "match_data_to_db ";
            $tmpCommand .= "--enum={$item['id']} ";
            $str .= $this->echoCommand($tmpCommand);
        }
        $str .= "\n";
        return $str;
    }



    protected function pullUserAction(){
        $cpTypeList = CpTypeEnums::$list;
        $str = "";

        //书城
        foreach ($cpTypeList as $cpType){
            $productTypeList = $cpType['product_type'] ?? [];
            //产品类型
            foreach ($productTypeList as $productType){
                $str .= "        //{$cpType['name']}-{$productType}\n";

                //用户行为
                $userActionList = $this->userActionMap[$productType];
                foreach($userActionList as $userAction){

                    // 目前先对接阅文
                    if( $cpType['id'] != CpTypeEnums::YW){
                        continue;
                    }

                    // 跳过阅文注册、加桌行为
                    if(
                        $cpType['id'] == CpTypeEnums::YW
                        && in_array($userAction,[UserActionTypeEnum::REG,UserActionTypeEnum::ADD_SHORTCUT,UserActionTypeEnum::COMPLETE_ORDER])){
                        continue;
                    }

                    $tmpCommand = "pull_user_action ";
                    $tmpCommand .= "--cp_type={$cpType['id']} --product_type={$productType} ";
                    $tmpCommand .= "--action_type={$userAction} ";
                    $tmpCommand .= "--time={\$timeRange}";
                    $str .= $this->echoCommand($tmpCommand);
                }
                $str .= "\n";
            }
        }
        return $str;
    }



    protected function pushUserAction(){
        $cpTypeList = CpTypeEnums::$list;

        $str = "";

        //书城
        foreach ($cpTypeList as $cpType){
            $productTypeList = $cpType['product_type'] ?? [];
            //产品类型
            foreach ($productTypeList as $productType){
                $str .= "        //{$cpType['name']}-{$productType}\n";

                //用户行为
                $userActionList = $this->userActionMap[$productType];
                foreach($userActionList as $userAction){
                    $tmpCommand = "push_user_action  ";
                    $tmpCommand .= "--cp_type={$cpType['id']} --product_type={$productType} ";
                    $tmpCommand .= "--action_type={$userAction} ";
                    $tmpCommand .= "--time={\$timeRange}";
                    $str .= $this->echoCommand($tmpCommand);
                }
                $str .= "\n";
            }
        }
        return $str;
    }



    protected function echoCommand($str){
        return  "        \$schedule->command(\"{$str}\")->cron('* * * * *');\n";
    }


}
