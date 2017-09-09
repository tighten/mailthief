<?php

namespace MailThief;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\ServiceProvider;

class MailThiefServiceProvider extends ServiceProvider
{
    public function register()
    {
        if ((float) $this->app->version() >= 5.5) {
            $this->app->singleton(MailThiefFiveFiveCompatible::class);
        } else {
            $this->app->singleton(MailThiefFiveFourCompatible::class);
        }
    }
}
