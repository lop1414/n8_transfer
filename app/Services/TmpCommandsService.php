<?php

namespace App\Services;


class TmpCommandsService
{


    /**
     * @param $schedule
     * 队列数据入库
     */
    public function userActionQueueDataToDb($schedule){
#commands|user_action_queue_data_to_db#
    }


    /**
     * @param $schedule
     * 匹配数据入库
     */
    public function matchQueueDataToDb($schedule){
#commands|match_queue_data_to_db#
    }


    /**
     * @param $schedule
     * @param $timeRange
     * 拉取用户行为数据
     */
    public function pullUserAction($schedule,$timeRange){
#commands|pull_user_action#
    }



}
