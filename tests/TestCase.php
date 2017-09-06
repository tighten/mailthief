<?php

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\View\Factory;
use MailThief\MailThiefFiveFourCompatible;

abstract class TestCase extends PHPUnit_Framework_TestCase {

    protected function getViewFactory()
    {
        return tap(Mockery::mock(Factory::class), function ($factory) {
            $factory->shouldReceive('make')
                ->andReturn($this->getView());
        });
    }

    public function getView()
    {
        return new class {
            public function render()
            {
                return 'stubbed rendered view';
            }
        };
    }

    protected function getConfigFactory()
    {
        $configKeys = [
            'mail.from.name' => 'First Last',
            'mail.from.address' => 'foo@bar.tld',
        ];

        $config = Mockery::mock(ConfigRepository::class);
        $config->shouldReceive('has')->andReturnUsing(function ($key) use ($configKeys) {
            if (isset($configKeys[$key])) {
                return true;
            }

            return false;
        });

        $config->shouldReceive('get')->andReturnUsing(function ($key) use ($configKeys) {
            if (isset($configKeys[$key])) {
                return $configKeys[$key];
            }

            return null;
        });

        return $config;
    }

    protected function getMailThief()
    {
        return new MailThiefFiveFourCompatible($this->getViewFactory(), $this->getConfigFactory());
    }

}