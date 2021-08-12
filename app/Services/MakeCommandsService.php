<?php

namespace App\Services;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Enums\QueueEnums;
use App\Enums\UserActionTypeEnum;

class MakeCommandsService
{

    protected $userActionList = [
        CpTypeEnums::YW => [
            ProductTypeEnums::KYY => [
                UserActionTypeEnum::ORDER,
            ],
            ProductTypeEnums::H5 => [
                UserActionTypeEnum::REG,
                UserActionTypeEnum::ORDER,
            ]
        ],
        CpTypeEnums::QY => [
            ProductTypeEnums::H5 => [
                UserActionTypeEnum::REG,
                UserActionTypeEnum::ORDER,
            ]
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
        $str = "";

        //用户行为
        foreach($this->userActionList as $cpType => $item){

            foreach ($item as $productType => $actions){
                $str .= "        //{$cpType}-{$productType}\n";

                foreach ($actions as $action){
                    $tmpCommand = "pull_user_action ";
                    $tmpCommand .= "--cp_type={$cpType} --product_type={$productType} ";
                    $tmpCommand .= "--action_type={$action} ";
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
