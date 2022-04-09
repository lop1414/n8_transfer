<?php

namespace App\Traits\UserAction;

use App\Enums\UserActionTypeEnum;

trait Follow
{

    public function getType(): string
    {
        return UserActionTypeEnum::FOLLOW;
    }
}
