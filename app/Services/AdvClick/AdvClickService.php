<?php

namespace App\Services\AdvClick;

use App\Common\Enums\AdvClickSourceEnum;
use App\Common\Services\BaseService;


class AdvClickService extends BaseService
{

    protected $adv;


    /**
     * @var string
     * 广告点击来源
     */
    protected $clickSource = AdvClickSourceEnum::N8_TRANSFER;


    public function save($data){}



}
