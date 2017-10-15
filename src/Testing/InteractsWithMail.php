<?php

namespace MailThief\Testing;

use MailThief\Facades\MailThief;
use MailThief\Message;
use MailThief\Support\MailThiefCollection;
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
        if (! $this->mailer) {
            $this->setMailer(MailThief::getFacadeRoot());
        }

        return $this->mailer;
    }

    /**
     * @return MailThiefCollection
     */
    public function getMessages()
    {
        return $this->getMailer()->messages;
    }

    /**
     * @param array|string $emails
     * @return MailThiefCollection
     */
    public function getMessagesFor($emails)
    {
        $emails = collect((array) $emails);

        return $this->getMessages()->filter(function (Message $message) use ($emails) {
            return $emails->contains(function ($email) use ($message) {
                return $message->hasRecipient($email);
            });
        });
    }

    /**
     * @param array|string $email
     * @return Message
     */
    public function getLastMessageFor($email)
    {
        return $this->getMessagesFor($email)->last();
    }

    /** @before */
    public function hijackMail()
    {
        if (method_exists($this, 'afterApplicationCreated')) {
            $this->afterApplicationCreated(function () {
                $this->getMailer()->hijack();
            });
        } else {
            $this->getMailer()->hijack();
        }
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

    public function seeMessageFrom($email, $name = NULL)
    {
        $this->seeMessage();

        $this->lastMessage()->from->each(function ($nameOrEmail, $emailOrIndex) use ($email, $name) {
            // If no name is specified, the structure is ['hello@example.org'], if specified, it's
            // ['hello@example.org' => 'From Example']
            $fromEmail = is_int($emailOrIndex) ? $nameOrEmail : $emailOrIndex;
            $fromName = is_int($emailOrIndex) ? null : $nameOrEmail;

            $this->assertEquals(
                $email,
                $fromEmail,
                sprintf(
                    'Expected to find message from "[%s]", but found "[%s]".',
                    $email,
                    $fromEmail
                )
            );

            if (! $name) {
                return;
            }

            $this->assertEquals(
                $name,
                $fromName,
                sprintf(
                    'Expected to find message from "[%s]", but found "[%s]".',
                    $name,
                    $fromName
                )
            );
        });

        return $this;
    }

    public function lastMessage()
    {
        return $this->getMailer()->lastMessage();
    }

    public function seeHeaders($name, $value = null)
    {
        $this->assertTrue($this->lastMessage()->headers->contains(function ($header) use ($name, $value) {
            if (is_null($value)) {
                return $header['name'] === $name;
            }

            return $header['name'] === $name && $header['value'] === $value;
        }));

        return $this;
    }

    protected function seeInSubjects($subjects)
    {
        $subjects = (array) $subjects;

        foreach ($subjects as $subject) {
            $this->assertTrue(in_array($subject, $this->getMailer()->subjects()->all()));
        }
    }

    protected function seeMessage()
    {
        $this->assertNotNull(
            $this->lastMessage(),
            'Unable to find a generated email.'
        );

        return $this;
    }

    protected function dontSeeMessage()
    {
        $this->assertNull(
            $this->lastMessage(),
            'Generated email found.'
        );

        return $this;
    }
}
