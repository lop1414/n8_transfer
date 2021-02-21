<?php

namespace App\Services;

use App\Common\Services\BaseService;
use App\Models\TmpUserActionLogModel;
use Illuminate\Support\Facades\DB;


class MakeUserActionLogsTableService extends BaseService
{

    public function make($year){

        for($month = 1; $month <= 12; $month++){
            if($month < 10){
                $month = str_pad($month,2,"0",STR_PAD_LEFT);
            }
            $suffix = $year.$month;
            $this->createTable($suffix);
        }
    }



    public function createTable($suffix){
        $tableName = (new TmpUserActionLogModel())->getTable();


        // 获取建表脚本
        $tmp = DB::select("SHOW CREATE TABLE {$tableName}");
        $sql = (array) $tmp[0];

        // 新表名
        $createTableName = str_replace('tmp_', '', $tableName);
        $createTableName .= '_'.$suffix;

        // 去除自增偏移
        $createTableSql = preg_replace('/AUTO_INCREMENT=(\d+)/','', $sql['Create Table']);

        // 去除表名
        $createTableSql = str_replace("CREATE TABLE `{$tableName}`",'', $createTableSql);

        // 建表
        $createTableSql = "CREATE TABLE IF NOT EXISTS `{$createTableName}` {$createTableSql}";
        DB::select($createTableSql);
    }
}
