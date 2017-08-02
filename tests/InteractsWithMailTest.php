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

    public function test_get_messages()
    {
        $mailer = $this->mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com');
        });

        $message = $this->getMessages()->first();

        static::assertEquals(['john@example.com'], $message->to->toArray());
    }

    public function test_get_messages_for()
    {
        $mailer = $this->mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com');
        });

        $mailer->send('example-view', [], function ($m) {
            $m->to(['john@example.com', 'jay@example.com']);
        });

        $mailer->send('example-view', [], function ($m) {
            $m->to('jay@example.com');
        });

        $messages = $this->getMessagesFor(['john@example.com']);

        static::assertEquals(2, $messages->count());

        foreach ($messages as $message) {
            static::assertTrue($message->to->contains('john@example.com'));
        }
    }

    public function test_get_last_message_for()
    {
        $mailer = $this->mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com');
        });

        $mailer->send('example-view', [], function ($m) {
            $m->to(['john@example.com', 'jay@example.com']);
        });

        $mailer->send('example-view', [], function ($m) {
            $m->to('jay@example.com');
        });

        $message = $this->getLastMessageFor(['john@example.com']);

        static::assertTrue($message->to->contains('john@example.com'));
        static::assertTrue($message->to->contains('jay@example.com'));
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

    public function test_see_in_subjects()
    {
        $mailer = $this->mailer = $this->getMailThief();

        collect(['foo@bar.tld', 'baz@qux.tld'])->each(function ($email) use ($mailer) {
            $mailer->send('example-view', [], function ($m) use ($email) {
                $m->subject("Message for {$email}");
            });
        });

        $this->seeInSubjects("Message for baz@qux.tld");
    }

    public function test_see_in_subjects_with_array()
    {
        $mailer = $this->mailer = $this->getMailThief();

        collect(['Taylor Otwell', 'Adam Wathan'])->each(function ($name) use ($mailer) {
            $mailer->send('example-view', [], function ($m) use ($name) {
                $m->subject("Message for {$name}");
            });
        });

        $this->seeInSubjects(["Message for Taylor Otwell", "Message for Adam Wathan"]);
    }

    public function test_see_message_from()
    {
        $mailer = $this->mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->from('me@example.com');
            $m->to('john@example.com');
        });

        $this->seeMessageFrom('me@example.com');

        $mailer->send('example-view', [], function ($m) {
            $m->from('me2@example.com', 'Name');
            $m->to('john@example.com');
        });

        $this->seeMessageFrom('me2@example.com');
    }

    public function test_see_headers_for()
    {
        $mailer = $this->mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com');
            $m->getHeaders()->addTextHeader('X-MailThief-Variables', json_encode(['mailthief_id' => 2]));
        });

        $this->seeHeaders('X-MailThief-Variables');
        $this->seeHeaders('X-MailThief-Variables', json_encode(['mailthief_id' => 2]));
    }

    public function test_dont_see_message()
    {
        $this->mailer = $this->getMailThief();

        $this->dontSeeMessage();
    }

    public function test_global_from()
    {
        $mailer = $this->mailer = $this->getMailThief();

        $mailer->alwaysFrom('me@example.com', 'Example Person');

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com');
        });

        $this->seeMessageFrom('me@example.com');
        $this->seeMessageFrom('me@example.com', 'Example Person');
    }
}
