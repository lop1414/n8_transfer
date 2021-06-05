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
     * @param $timeRange
     * 拉取用户行为数据
     */
    public function pullUserAction($schedule,$timeRange){
#commands|pull_user_action#
    }


    /**
     * @param $schedule
     * @param $timeRange
     * 推送用户行为
     */
    public function pushUserAction($schedule,$timeRange){
#commands|push_user_action#
    }


}
