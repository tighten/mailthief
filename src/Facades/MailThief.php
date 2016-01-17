<?php

namespace MailThief\Facades;

use Illuminate\Support\Facades\Facade;

class MailThief extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \MailThief\MailThief::class;
    }
}
