<?php

namespace App\Traits\UserAction;

use App\Enums\UserActionTypeEnum;

trait CompleteOrder
{

    public function getType(): string
    {
        return UserActionTypeEnum::COMPLETE_ORDER;
    }
}
