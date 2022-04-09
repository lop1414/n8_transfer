<?php

namespace App\Traits\UserAction;

use App\Enums\UserActionTypeEnum;

trait Order
{

    public function getType(): string
    {
        return UserActionTypeEnum::ORDER;
    }
}
