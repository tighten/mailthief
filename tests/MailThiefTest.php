<?php

use Illuminate\Contracts\View\Factory;
use MailThief\MailThief;

class MailThiefTest extends PHPUnit_Framework_TestCase
{
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

    public function test_send_to_one_recipient()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com');
        });

        $this->assertTrue($mailer->hasMessageFor('john@example.com'));
    }

    public function test_send_to_multiple_recipients()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to(['john@example.com', 'jane@example.com']);
        });

        $this->assertTrue($mailer->hasMessageFor('john@example.com'));
        $this->assertTrue($mailer->hasMessageFor('jane@example.com'));
    }

    public function test_send_to_multiple_recipients_in_sequence()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com')->to('jane@example.com');
        });

        $this->assertTrue($mailer->hasMessageFor('john@example.com'));
        $this->assertTrue($mailer->hasMessageFor('jane@example.com'));
    }

    public function test_cc()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com')->cc('jane@example.com');
        });

        $this->assertTrue($mailer->hasMessageFor('john@example.com'));
        $this->assertTrue($mailer->hasMessageFor('jane@example.com'));
    }

    public function test_bcc()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com')->cc('jane@example.com')->bcc('joe@example.com');
        });

        $this->assertTrue($mailer->hasMessageFor('john@example.com'));
        $this->assertTrue($mailer->hasMessageFor('jane@example.com'));
        $this->assertTrue($mailer->hasMessageFor('joe@example.com'));
    }

    public function test_from_returns_array_of_froms()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com');
            $m->from('jane@example.com');
        });

        $this->assertEquals(['jane@example.com'], $mailer->lastMessage()->from->all());
    }

    public function test_from_can_be_multiple()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com');
            $m->from(['jane@example.com', 'joe@example.com']);
        });

        $this->assertEquals(['jane@example.com', 'joe@example.com'], $mailer->lastMessage()->from->all());
    }

    public function test_from_overrides_previous_from()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com');
            $m->from(['joe@example.com']);
            $m->from(['jane@example.com']);
        });

        $this->assertEquals(['jane@example.com'], $mailer->lastMessage()->from->all());
    }

    public function test_sender_returns_array_of_senders()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com');
            $m->sender('jane@example.com');
        });

        $this->assertEquals(['jane@example.com'], $mailer->lastMessage()->sender->all());
    }

    public function test_sender_can_be_multiple()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com');
            $m->sender(['jane@example.com', 'joe@example.com']);
        });

        $this->assertEquals(['jane@example.com', 'joe@example.com'], $mailer->lastMessage()->sender->all());
    }

    public function test_sender_overrides_previous_from()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com');
            $m->sender(['joe@example.com']);
            $m->sender(['jane@example.com']);
        });

        $this->assertEquals(['jane@example.com'], $mailer->lastMessage()->sender->all());
    }

    public function test_reply_to_one()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->replyTo('john@example.com');
        });

        $this->assertEquals(['john@example.com'], $mailer->lastMessage()->reply_to->all());
    }

    public function test_reply_to_many()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->replyTo(['john@example.com', 'jane@example.com']);
        });

        $this->assertEquals(['john@example.com', 'jane@example.com'], $mailer->lastMessage()->reply_to->all());
    }

    public function test_reply_to_chained()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->replyTo('john@example.com')->replyTo('jane@example.com');
        });

        $this->assertEquals(['john@example.com', 'jane@example.com'], $mailer->lastMessage()->reply_to->all());
    }

    public function test_queue_is_sent_immediately()
    {
        $mailer = $this->getMailThief();

        $mailer->queue('example-view', [], function ($m) {
            $m->to('john@example.com');
        });

        $this->assertTrue($mailer->hasMessageFor('john@example.com'));
    }

    public function test_later_messages_are_marked_with_delay()
    {
        $mailer = $this->getMailThief();

        $mailer->later(10, 'example-view', [], function ($m) {
            $m->to('john@example.com');
        });

        $this->assertEquals(10, $mailer->later->first()->delay);
    }

    public function test_later_messages_are_not_included_when_checking_to_see_if_an_email_was_sent()
    {
        $mailer = $this->getMailThief();

        $mailer->later(10, 'example-view', [], function ($m) {
            $m->to('john@example.com');
        });

        $this->assertFalse($mailer->hasMessageFor('john@example.com'));
    }

    public function test_can_retrieve_last_sent_message()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->subject('First message');
        });

        $mailer->send('example-view', [], function ($m) {
            $m->subject('Second message');
        });

        $this->assertEquals('Second message', $mailer->lastMessage()->subject);
    }

    public function test_attachments()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->attach('invoice.pdf');
        });

        $this->assertEquals('invoice.pdf', $mailer->lastMessage()->attachments[0]['path']);
    }


    public function test_cc_and_bcc_are_considered_recipients()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com')->cc('jane@example.com')->bcc('joe@example.com');
        });

        $message = $mailer->lastMessage();
        $this->assertTrue($message->hasRecipient('jane@example.com'));
        $this->assertTrue($message->hasRecipient('joe@example.com'));
    }

    public function test_contains_in_html()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com');
        });

        $this->assertTrue($mailer->lastMessage()->contains('stubbed'));
    }

    public function test_contains_in_raw()
    {
        $mailer = $this->getMailThief();

        $mailer->raw('Some kind of raw text mail content', function ($m) {
            $m->to('john@example.com');
        });

        $this->assertTrue($mailer->lastMessage()->contains('kind', 'raw'));
    }

    public function test_get_body_html()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com');
        });

        $this->assertEquals('stubbed rendered view', $mailer->lastMessage()->getBody());
    }

    public function test_get_body_raw()
    {
        $mailer = $this->getMailThief();

        $mailer->raw('Raw text content', function ($m) {
            $m->to('john@example.com');
        });

        $this->assertEquals('Raw text content', $mailer->lastMessage()->getBody());
    }

    public function test_valid_message_method_not_in_mailthief_return_this_instance()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->subject('Second message');
            $this->assertEquals($m, $m->addPart('html content', 'text/html'));
        });
    }

    /**
     * @expectedException Exception
     */
    public function test_invalid_message_method_in_mailthief_causes_exception()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->subject('Second message');
            $m->foo('bar');
        });
    }
}
