<?php

namespace App\Console;


use App\Console\Commands\CreateTableCommand;
use App\Console\Commands\MakeCommandCommand;
use App\Console\Commands\UserActionCommand;
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

        // 用户行为 拉取 或 推送
        UserActionCommand::class,

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
        $tmpTime = date('Y-m-d H:i:s',TIMESTAMP-60);
        $oneMinuteRange = "'{$tmpTime}','{$dateTime}'";
        //半小时区间
        $halfHourRange = '"'.date('Y-m-d H:i:s',TIMESTAMP-60). '","'. $dateTime.'"';

        //用户行为数据 拉取 及 上报
        $path = base_path(). '/app/Services/CommandsService.php';
        if(file_exists($path)){
            $commandsService = new \App\Services\CommandsService();
            $commandsService->pullUserAction($schedule,$oneMinuteRange);
            $commandsService->pushUserAction($schedule,$halfHourRange);
        }

    }


}
