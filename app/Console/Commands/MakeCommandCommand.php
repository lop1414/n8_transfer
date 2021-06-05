<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Services\MakeCommandsService;

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



    public function handle(){

        (new MakeCommandsService())->make();

    }





}
