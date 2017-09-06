<?php

use Illuminate\Contracts\View\Factory;
use MailThief\MailThiefFiveFourCompatible;
use MailThief\NullMessageForView;

class MailThiefTest extends TestCase
{
    public function test_send_to_one_recipient()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com');
        });

        $this->assertTrue($mailer->hasMessageFor('john@example.com'));
    }
    
    public function test_overwrite_recipient()
    {
        $mailer = $this->getMailThief();

        $mailer->send('example-view', [], function ($m) {
            $m->to('john@example.com')->to('jane@example.com', null, true);
        });

        $this->assertFalse($mailer->hasMessageFor('john@example.com'));
        $this->assertTrue($mailer->hasMessageFor('jane@example.com'));
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

    public function test_global_from_is_respected()
    {
        $mailer = $this->getMailThief();

        $mailer->alwaysFrom('john@example.com');

        $mailer->send('example-view', [], function ($m) {
            $m->to('joe@example.com');
        });

        $this->assertEquals(['john@example.com'], $mailer->lastMessage()->from->all());
    }

    public function test_global_from_gets_overwritten_if_specified()
    {
        $mailer = $this->getMailThief();

        $mailer->alwaysFrom('john@example.com');

        $mailer->send('example-view', [], function ($m) {
            $m->to('joe@example.com');
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

    public function test_queue_on_is_sent_immediately()
    {
        $mailer = $this->getMailThief();

        $mailer->queueOn('queue-name', 'example-view', [], function ($m) {
            $m->to('john@example.com');
        });

        $mailer->queueOn('queue-name', 'example-view', [], function ($m) {
            $m->to('john@example2.com');
        });

        $this->assertTrue($mailer->hasMessageFor('john@example.com'));
        $this->assertTrue($mailer->hasMessageFor('john@example2.com'));
    }

    public function test_global_from_is_respected_when_email_is_queued()
    {
        $mailer = $this->getMailThief();

        $mailer->alwaysFrom('john@example.com');

        $mailer->queue('example-view', [], function ($m) {
            $m->to('joe@example.com');
        });

        $this->assertEquals(['john@example.com'], $mailer->lastMessage()->from->all());
    }

    public function test_later_messages_are_marked_with_delay()
    {
        $mailer = $this->getMailThief();

        $mailer->later(10, 'example-view', [], function ($m) {
            $m->to('john@example.com');
        });

        $this->assertEquals(10, $mailer->later->first()->delay);
    }

    public function test_later_on_messages_are_marked_with_delay()
    {
        $mailer = $this->getMailThief();

        $mailer->laterOn('queue-name', 10, 'example-view', [], function ($m) {
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

    public function test_global_from_is_respected_when_email_set_to_later_with_a_delay()
    {
        $mailer = $this->getMailThief();

        $mailer->alwaysFrom('john@example.com');

        $mailer->later(10, 'example-view', [], function ($m) {
            $m->to('joe@example.com');
        });

        $this->assertEquals(['john@example.com'], $mailer->later->first()->from->all());
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

    public function test_email_view_automatically_receives_null_message_object()
    {
        $viewFactory = Mockery::mock(Factory::class);
        $viewFactory->shouldReceive('make')
            ->with(Mockery::any(), Mockery::on(function ($arg) {
                return $arg['message'] instanceof NullMessageForView;
            }))
            ->andReturn($this->getView());

        $mailer = new MailThiefFiveFourCompatible($viewFactory, $this->getConfigFactory());;

        $mailer->send('example-view', [], function ($m) {
            $m->subject('Second message');
        });
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

    public function test_it_reads_values_from_the_config_helper_function()
    {
        $mailer = \Mockery::mock('MailThief\MailThief[swapMail]', [$this->getViewFactory(), $this->getConfigFactory()])
                    ->shouldAllowMockingProtectedMethods();
        $mailer->shouldReceive('swapMail')->once()->andReturn(null);

        $mailer->hijack();

        $mailer->send('example-view', [], function ($m) {
            $m->to('joe@example.com');
        });

        $this->assertEquals(['foo@bar.tld' => 'First Last'], $mailer->lastMessage()->from->all());
    }

    public function test_it_gets_subjects()
    {
        $mailer = $this->getMailThief();

        collect(['foo@bar.tld', 'baz@qux.tld'])->each(function ($email) use ($mailer) {
            $mailer->send('example-view', [], function ($m) use ($email) {
                $m->subject("Message for {$email}");
            });
        });

        $messages = ["Message for foo@bar.tld", "Message for baz@qux.tld"];

        $this->assertEquals($messages, $mailer->subjects()->all());
    }
}
