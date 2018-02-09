<?php

namespace MailThief\Facades;

use Illuminate\Support\Facades\Facade;
use MailThief\MailThiefFiveFiveCompatible;
use MailThief\MailThiefFiveFourCompatible;

class MailThief extends Facade
{
    protected static function getFacadeAccessor()
    {
    	$appVersion = self::$app->version();

		$versions = [];

		// Take the first version from whatever string we are passed.
		preg_match("/[0-9]\.[0-9]/", $appVersion, $versions);
		$appVersion = $versions[0];
    	
    	$appVersion = (float) $appVersion;


        return $appVersion >= 5.5
            ? MailThiefFiveFiveCompatible::class
            : MailThiefFiveFourCompatible::class;
    }
}
