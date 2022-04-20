<?php

namespace App\Console;


use App\Common\Enums\AdvAliasEnum;
use App\Console\Commands\CheckUserActionCommand;
use App\Console\Commands\CreateTableCommand;
use App\Console\Commands\FillUserActionChannelCommand;
use App\Console\Commands\ForwardDataCommand;
use App\Console\Commands\MatchDataToDbCommand;
use App\Console\Commands\PushAdvClickCommand;
use App\Console\Commands\PullUserActionCommand;
use App\Console\Commands\PushUserActionCommand;
use App\Console\Commands\UserActionDataToDbCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [

        CreateTableCommand::class,

        // 队列行为数据入库
        UserActionDataToDbCommand::class,
        // 匹配数据入库
        MatchDataToDbCommand::class,
        // 拉取用户行为
        PullUserActionCommand::class,
        // 补充用户行为渠道
        FillUserActionChannelCommand::class,
        // 监测用户行为数据
        CheckUserActionCommand::class,

        // 推送用户行为
        PushUserActionCommand::class,


        // 推送点击数据
        PushAdvClickCommand::class,



        // 转发数据
        ForwardDataCommand::class,

    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //创建分表
        $schedule->command('create_table')->cron('0 0 1,15 * *');

        //时间范围
        $dateTime = date('Y-m-d H:i:s',TIMESTAMP);
        //一分钟区间
        $oneMinuteRange = "'".date('Y-m-d H:i:s',TIMESTAMP-60)."','{$dateTime}'";
        //二分钟区间
        $twoMinuteRange = "'".date('Y-m-d H:i:s',TIMESTAMP-60*2)."','{$dateTime}'";
        //五分钟区间
        $fiveMinuteRange = "'".date('Y-m-d H:i:s',TIMESTAMP-60*5)."','{$dateTime}'";
        //十分钟区间
        $tenMinuteRange = "'".date('Y-m-d H:i:s',TIMESTAMP-60*10)."','{$dateTime}'";
        //半小时区间
        $halfHourRange = "'".date('Y-m-d H:i:s',TIMESTAMP-60*30)."','{$dateTime}'";
        //3小时区间
        $threeHourRange = "'".date('Y-m-d H:i:s',TIMESTAMP-60*60*3)."','{$dateTime}'";




        // 用户行为数据 start

        // 队列入库
        $schedule->command("user_action_data_to_db --enum=USER_REG_ACTION ")->cron('* * * * *');
        $schedule->command("user_action_data_to_db --enum=USER_ADD_SHORTCUT_ACTION ")->cron('* * * * *');

        // 同步
        // -- 注册
        $schedule->command("pull_user_action --cp_type=YW --product_type=H5 --action_type=REG --time={$fiveMinuteRange}")->cron('*/5 * * * *');
        $schedule->command("pull_user_action --cp_type=TW --product_type=APP --action_type=REG --time={$fiveMinuteRange}")->cron('*/5 * * * *');
        $schedule->command("pull_user_action --cp_type=TW --product_type=KYY --action_type=REG --time={$fiveMinuteRange}")->cron('*/5 * * * *');
        $schedule->command("pull_user_action --cp_type=QY --product_type=H5 --action_type=REG --time={$fiveMinuteRange}")->cron('*/5 * * * *');
        $schedule->command("pull_user_action --cp_type=FQ --product_type=KYY --action_type=REG --time={$fiveMinuteRange}")->cron('*/5 * * * *');
        $schedule->command("pull_user_action --cp_type=BM --product_type=KYY --action_type=REG --time={$fiveMinuteRange}")->cron('*/5 * * * *');
        // -- 订单
        $schedule->command("pull_user_action --action_type=ORDER --time={$fiveMinuteRange}")->cron('*/5 * * * *');

        // 查漏补缺
        $tmpRange =  "'".date('Y-m-d H:i:s',TIMESTAMP - 60*60*48)."','".date('Y-m-d H:i:s',TIMESTAMP - 60*60)."'";
        $schedule->command("check_user_action --time={$tmpRange}")->cron('*/2 * * * *');


        //补充用户行为的渠道信息等 阅文
        $tmp = "'".date('Y-m-d H:i:s',TIMESTAMP-60*12)."','".date('Y-m-d H:i:s',TIMESTAMP)."'";
        $schedule->command("fill_user_action_channel --product_type=KYY --time={$tmp}")->cron('*/10 * * * *');
        $schedule->command("fill_user_action_channel --product_type=H5 --time={$tmp}")->cron('*/10 * * * *');


        //上报
        $schedule->command("push_user_action")->cron('* * * * *');


        // 用户行为数据 end




        // 匹配数据入库
        // -- 头条
        $schedule->command("match_data_to_db --enum=OCEAN_MATCH_DATA ")->cron('* * * * *');
        // -- 快手
        $schedule->command("match_data_to_db --enum=KUAI_SHOU_MATCH_DATA ")->cron('* * * * *');


        //广告商点击数据上报
        $schedule->command("push_adv_click --adv_alias=".AdvAliasEnum::OCEAN)->cron('* * * * *');
        $schedule->command("push_adv_click --adv_alias=".AdvAliasEnum::BD)->cron('* * * * *');
        $schedule->command("push_adv_click --adv_alias=".AdvAliasEnum::KS)->cron('* * * * *');


        // 转发数据
        $schedule->command("forward_data")->cron('* * * * *');
    }


}
