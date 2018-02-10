<?php

namespace MailThief\Facades;

use Illuminate\Support\Facades\Facade;
use MailThief\MailThiefFiveFiveCompatible;
use MailThief\MailThiefFiveFourCompatible;

class MailThief extends Facade
{
    protected static function getFacadeAccessor()
    {
        return self::normalizeAppVersion(self::$app->version()) >= 5.5
            ? MailThiefFiveFiveCompatible::class
            : MailThiefFiveFourCompatible::class;
    }

    public static function normalizeAppVersion($version)
    {
        preg_match("/[0-9]*\.[0-9]*/", $version, $versions);

        return (float) reset($versions);
    }
}
