<?php

namespace App\Services;

use App\Common\Services\BaseService;
use Illuminate\Support\Facades\DB;

class CreateTableService extends BaseService
{

    protected $suffix;


    public function setSuffix($suffix){
        $this->suffix = $suffix;
    }



    public function create(){
        $dbDatabase = config('database.connections.mysql.database');

        $rows = DB::select("SELECT table_name FROM information_schema.tables
    WHERE table_schema = '{$dbDatabase}' AND table_name LIKE 'tmp_%'");

        foreach($rows as $row){
            // 获取建表脚本
            $tmp = DB::select("SHOW CREATE TABLE {$dbDatabase}.{$row->table_name}");
            $sql = (array) $tmp[0];

            // 新表名
            $createTableName = str_replace('tmp_', '', $row->table_name);
            $createTableName .= '_'.$this->suffix;

            // 去除自增偏移
            $createTableSql = preg_replace('/AUTO_INCREMENT=(\d+)/','', $sql['Create Table']);

            // 去除表名
            $createTableSql = str_replace("CREATE TABLE `{$row->table_name}`",'', $createTableSql);

            // 建表
            $createTableSql = "CREATE TABLE IF NOT EXISTS {$dbDatabase}.`{$createTableName}` {$createTableSql}";
            DB::select($createTableSql);
        }
    }


}
