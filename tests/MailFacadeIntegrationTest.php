<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Mail;
use MailThief\Facades\MailThief;
use MailThief\Testing\InteractsWithMail;
use MailThief\MailThiefFiveFourCompatible;

class MailFacadeIntegrationTest extends TestCase
{
    use InteractsWithMail;

    public function setUp()
    {
        $app = new AppStub;
        $app->setAttributes([
            MailThiefFiveFourCompatible::class => $this->getMailThief(),
        ]);

        Facade::setFacadeApplication($app);

        parent::setUp();
    }

    public function test_previously_set_instance_of_mailthief_doesnt_change()
    {
        Mail::send('example-view', [], function ($m) {
            $m->to('john@example.com');
        });

        $this->seeMessageFor('john@example.com');

        MailThief::swap($this->getMailThief());

        Mail::send('example-view', [], function ($m) {
            $m->to('jay@example.com');
        });

        $this->seeMessageFor('jay@example.com');
    }
}
