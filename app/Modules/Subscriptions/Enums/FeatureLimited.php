<?php

namespace App\Modules\Subscriptions\Enums;

enum FeatureLimited: int
{
    case unlimited = 0;
    case limited   = 1;
}
