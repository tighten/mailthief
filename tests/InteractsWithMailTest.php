<?php

use Illuminate\Contracts\View\Factory;
use MailThief\MailThief;
use MailThief\Testing\InteractsWithMail;

class InteractsWithMailTest extends PHPUnit_Framework_TestCase
{
    use InteractsWithMail;

    private function getViewFactory()
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

    private function getMailThief()
    {
        return new MailThief($this->getViewFactory());
    }

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
