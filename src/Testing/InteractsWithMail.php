<?php

namespace MailThief\Testing;

use MailThief\Facades\MailThief;
use Illuminate\Contracts\Mail\Mailer;

trait InteractsWithMail
{
    private $mailer;

    private function setMailer(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    private function getMailer()
    {
        return $this->mailer ?: MailThief::getFacadeRoot();
    }

    /** @before */
    public function hijackMail()
    {
        $this->getMailer()->hijack();
    }

    public function seeMessageFor($email)
    {
        $this->seeMessage();

        $this->assertTrue(
            $this->getMailer()->hasMessageFor($email),
            sprintf('Unable to find an email addressed to [%s].', $email)
        );

        return $this;
    }

    public function seeMessageWithSubject($subject)
    {
        $this->seeMessage();

        $lastSubject = $this->lastMessage()->subject;

        $this->assertEquals(
            $subject,
            $lastSubject,
            sprintf(
                'Expected subject to be "[%s]", but found "[%s]".',
                $subject,
                $lastSubject
            )
        );

        return $this;
    }

    public function seeMessageFrom($email)
    {
        $this->seeMessage();

        $from = $this->lastMessage()->from->first();

        $this->assertEquals(
            $email,
            $from,
            sprintf(
                'Expected to find message from "[%s]", but found "[%s]".',
                $email,
                $from
            )
        );

        return $this;
    }

    public function lastMessage()
    {
        return $this->getMailer()->lastMessage();
    }

    protected function seeMessage()
    {
        $this->assertNotNull(
            $this->lastMessage(),
            'Unable to find a generated email.'
        );

        return $this;
    }
}
