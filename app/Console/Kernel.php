<?php

namespace App\Console;


use App\Common\Enums\AdvAliasEnum;
use App\Console\Commands\CreateTableCommand;
use App\Console\Commands\FillUserActionInfoCommand;
use App\Console\Commands\MakeCommandCommand;
use App\Console\Commands\MatchDataToDbCommand;
use App\Console\Commands\PushAdvClickCommand;
use App\Console\Commands\PushChannelCommand;
use App\Console\Commands\PushChannelExtendCommand;
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
        MakeCommandCommand::class,

        // 队列行为数据入库
        UserActionDataToDbCommand::class,
        // 匹配数据入库
        MatchDataToDbCommand::class,
        // 拉取用户行为
        PullUserActionCommand::class,
        // 推送用户行为
        PushUserActionCommand::class,

        // 推送点击数据
        PushAdvClickCommand::class,

        // 推送渠道
        PushChannelCommand::class,

        // 推送渠道扩展信息
        PushChannelExtendCommand::class,

        // 补充用户信息
        FillUserActionInfoCommand::class

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
        //半小时区间
        $halfHourRange = "'".date('Y-m-d H:i:s',TIMESTAMP-60*30)."','{$dateTime}'";



        //广告商点击数据上报
//        $schedule->command("push_adv_click --adv_alias=".AdvAliasEnum::OCEAN." --time={$halfHourRange}")->cron('*/5 * * * *');


        //用户行为数据 拉取 及 上报
        $path = base_path(). '/app/Services/CommandsService.php';
        if(file_exists($path)){
            $commandsService = new \App\Services\CommandsService();
            $commandsService->userActionQueueDataToDb($schedule);
            $commandsService->matchQueueDataToDb($schedule);
//            $commandsService->pullUserAction($schedule,$twoMinuteRange);
//            $commandsService->pushUserAction($schedule,$halfHourRange);
        }

    }


}
