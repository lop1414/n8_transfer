<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Enums\DataSourceEnums;
use App\Models\UserActionLogModel;
use App\Services\CreateTableService;
use App\Services\SecondVersionYwKyy\UserRegActionService;

class CreateTableCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'create_table {--date=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '创建表';

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
        $this->update();die;
        $service = new CreateTableService();

        $date    = $this->option('date');

        $dateList = [];
        if(!empty($date)){
            $dateRange = Functions::getDateRange($date);

            $dateList = Functions::getMonthListByRange($dateRange,'Ym');
        }else{
            $dateList[] = date('Ym',strtotime('+1 month'));
        }

        foreach ($dateList as $item){
            echo " 创建:{$item}\n";
            $service->setSuffix($item);
            $service->create();
        }
    }



    public function update(){
        $model = new UserActionLogModel();
        $arr = [
            '1970-01-01',
            '2020-09-01',
            '2020-10-01',
            '2020-11-01',
            '2020-12-01',
            '2021-01-01',
            '2021-02-01',
            '2021-03-01',
            '2021-04-01',
            '2021-05-01'
        ];

        $service = new UserRegActionService();
        foreach ($arr as $date){
            do{
                $list = $model->setTableNameWithMonth($date)
                    ->where('extend','')
                    ->skip(0)
                    ->take(1000)
                    ->get();
                foreach ($list as $item){
                    $data = $item['data'];
                    if($item->source == DataSourceEnums::SECOND_VERSION){
                        $rawData = $data['extend'] ?? [];
                        $item->extend = $service->filterExtendInfo([
                            'ua'            => $rawData['user_info']['ua'] ?? '',
                            'muid'          => $rawData['user_info']['muid'] ?? '',
                            'android_id'    => $rawData['extend']['android_id'] ?? '',
                        ]);
                    }elseif ($item->source == DataSourceEnums::CP){
                        $item->extend = $service->filterExtendInfo([
                            'oaid'          => $data['oaid'] ?? '',
                            'device_manufacturer'  => $data['manufacturer'] ?? '',
                        ]);
                    }

                    $item->save();
                    echo "更新成功 {$item->open_id}\n";
                }
            }while(!$list->isEmpty());
        }

    }


}
