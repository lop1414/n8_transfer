<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Common\Services\ConsoleEchoService;
use App\Enums\UserActionTypeEnum;

class MakeCommandCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'make_command';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '创建命令';


    protected $userActionMap = [
        ProductTypeEnums::KYY => [
            UserActionTypeEnum::REG,
//            UserActionTypeEnum::READ,
            UserActionTypeEnum::ADD_SHORTCUT,
//            UserActionTypeEnum::LOGIN,
            UserActionTypeEnum::ORDER,
            UserActionTypeEnum::COMPLETE_ORDER
        ],
        ProductTypeEnums::H5 => [
            UserActionTypeEnum::REG,
//            UserActionTypeEnum::READ,
//            UserActionTypeEnum::FOLLOW,
//            UserActionTypeEnum::LOGIN,
            UserActionTypeEnum::ORDER,
            UserActionTypeEnum::COMPLETE_ORDER
        ]
    ];

    protected $consoleEchoService;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
        $this->consoleEchoService = new ConsoleEchoService();
    }



    public function handle(){

        $path = base_path(). '/app/Services/';
        $content = file_get_contents($path.'TmpCommandsService.php');

        $content = str_replace('TmpCommandsService','CommandsService',$content);

        $str = $this->pullUserAction();
        $content = str_replace('#commands|pull_user_action#',$str,$content);

        $str = $this->pushUserAction();
        $content = str_replace('#commands|push_user_action#',$str,$content);

        $fileName = $path.'CommandsService.php';
        file_put_contents($fileName,$content);
    }


    public function pullUserAction(){
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

                    // 跳过阅文注册、加桌行为
                    if(
                        $cpType['id'] == CpTypeEnums::YW
                        && in_array($userAction,[UserActionTypeEnum::REG,UserActionTypeEnum::ADD_SHORTCUT])){
                        continue;
                    }

                    $tmpCommand = "pull_user_action";
                    $tmpCommand .= "--cp_type={$cpType['id']} --product_type={$productType} ";
                    $tmpCommand .= "--action_type={$userAction} ";
                    $tmpCommand .= "--time={\$timeRange}";
                    $str .= $this->echoCommand($tmpCommand);
                }
                $str .= "\n";


                // 阅文注册加桌数据 需从二版拿
                if($cpType['id'] == CpTypeEnums::YW ){
                    $str .= "        //{$cpType['name']}(二版)-{$productType}\n";

                    $userActions = [UserActionTypeEnum::REG];

                    if($productType == ProductTypeEnums::KYY){
                        $userActions[] = UserActionTypeEnum::ADD_SHORTCUT;
                    }
                    //用户行为
                    foreach($userActions as $userAction){
                        $tmpCommand = "pull_user_action ";
                        $tmpCommand .= "--cp_type={$cpType['id']} --product_type={$productType} ";
                        $tmpCommand .= "--action_type={$userAction} ";
                        $tmpCommand .= "--time={\$timeRange} ";
                        $tmpCommand .= "--second_version=1";
                        $str .= $this->echoCommand($tmpCommand);
                    }
                    $str .= "\n";
                }
            }
        }
        return $str;
    }



    public function pushUserAction(){
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



    public function echoCommand($str){
        return  "        \$schedule->command(\"{$str}\")->cron('* * * * *');\n";
    }


}
