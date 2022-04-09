<?php

namespace App\Traits\UserAction;

use App\Enums\UserActionTypeEnum;

trait Reg
{

    public function getType(): string
    {
        return UserActionTypeEnum::REG;
    }
}
