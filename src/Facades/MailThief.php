<?php

namespace MailThief\Facades;

use Illuminate\Support\Facades\Facade;
use MailThief\MailThiefFiveFiveCompatible;
use MailThief\MailThiefFiveFourCompatible;

class MailThief extends Facade
{
    protected static function getFacadeAccessor()
    {
        return (float) self::$app->version() >= 5.5
            ? MailThiefFiveFiveCompatible::class
            : MailThiefFiveFourCompatible::class;
    }
}
