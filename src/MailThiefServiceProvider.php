<?php

namespace MailThief;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\ServiceProvider;

class MailThiefServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(MailThief::class);
    }
}
