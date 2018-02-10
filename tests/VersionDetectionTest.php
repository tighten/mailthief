<?php

use MailThief\Facades\MailThief;

class VersionDetectionTest extends TestCase
{
    public function test_gets_laravel_versions()
    {
        $this->assertEquals(5.5, MailThief::normalizeAppVersion('5.5'));
        $this->assertEquals(10.4, MailThief::normalizeAppVersion('10.4.1423'));
        $this->assertEquals(6.12, MailThief::normalizeAppVersion('6.12.1'));
    }

    public function test_gets_lumen_versions()
    {
        $this->assertEquals(5.6, MailThief::normalizeAppVersion('Lumen (5.6.1) (Laravel Components 5.6.*)'));
        $this->assertEquals(5.12, MailThief::normalizeAppVersion('Lumen (5.12.4) (Laravel Components 5.99.*)'));
        $this->assertEquals(10.2, MailThief::normalizeAppVersion('Lumen 10.2.123'));
        $this->assertEquals(4.20, MailThief::normalizeAppVersion('Version 4.20.1423 of the Esteemd Lumen Framework, Blessed Be It'));
    }
}
