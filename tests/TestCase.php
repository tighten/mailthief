<?php

use MailThief\MailThief;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

abstract class TestCase extends PHPUnit_Framework_TestCase {

    protected function getViewFactory()
    {
        $factory = Mockery::mock(Factory::class);
        $factory->shouldReceive('make')->andReturnUsing(function ($template, $data) {
            return new class {
                public function render()
                {
                    return 'stubbed rendered view';
                }
            };
        });
        return $factory;
    }

    protected function getConfigFactory()
    {
        $configKeys = [
            'mail.from.name' => 'First Last',
            'mail.from.address' => 'foo@bar.tld',
        ];

        $config = Mockery::mock(ConfigRepository::class);
        $config->shouldReceive('has')->andReturnUsing(function ($key) use ($configKeys) {
            if( isset($configKeys[$key]) ){
                return true;
            }

            return false;
        });

        $config->shouldReceive('get')->andReturnUsing(function ($key) use ($configKeys) {
            if( isset($configKeys[$key]) ){
                return $configKeys[$key];
            }

            return null;
        });

        return $config;
    }

    protected function getMailThief()
    {
        return new MailThief($this->getViewFactory(), $this->getConfigFactory());
    }

}