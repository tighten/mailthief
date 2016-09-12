<?php

use MailThief\MailThief;
use MailThief\Testing\InteractsWithMail;

class InteractsWithMailTest extends TestCase
{
    use InteractsWithMail;

    private function getMockMailThief()
    {
        return Mockery::mock(MailThief::class);
    }

    /**
     * Override this method so that it is not called before each test
     */
    public function hijackMail()
    {
        return;
    }

    public function test_hijack_mail()
    {
        $mailer = $this->mailer = $this->getMockMailThief();

        $mailer->shouldReceive('hijack');

        $this->hijackMail();
    }

    public function test_see_has_message_for()
    {
        $mailer = $this->mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com');
        });

        $this->seeMessageFor('john@example.com');
    }

    public function test_see_message_with_subject()
    {
        $mailer = $this->mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->subject('foo');
            $m->to('john@example.com');
        });

        $this->seeMessageWithSubject('foo');
    }

    public function test_see_message_from()
    {
        $mailer = $this->mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->from('me@example.com');
            $m->to('john@example.com');
        });

        $this->seeMessageFrom('me@example.com');
    }
}
