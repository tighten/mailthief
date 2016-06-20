<?php

namespace MailThief;

use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\View\Factory;

class MailThief implements Mailer
{
    private $views;
    public $messages;

    public function __construct(Factory $views)
    {
        $this->views = $views;
        $this->messages = collect();
    }

    public function hijack()
    {
        Mail::swap($this);
    }

    public function raw($text, $callback)
    {
        $message = Message::fromRaw($text);
        $callback($message);
        $this->messages[] = $message;
    }

    public function send($view, array $data, $callback)
    {
        $message = Message::fromView($view, $data, $this->views);
        $callback($message);
        $this->messages[] = $message;
    }

    public function failures()
    {
        // Impossible to detect failed recipients since no mail is actually sent.
        return [];
    }

    public function queue($view, array $data, $callback, $queue = null)
    {
        // @todo: Down the road it might be important to log the queue that is
        // meant to be used for people to assert against.
        return $this->send($view, $data, $callback);
    }

    public function later($delay, $view, array $data, $callback, $queue = null)
    {
        throw new Exception("Method 'later' is not implemented yet.");
    }

    public function hasMessageFor($email)
    {
        return $this->messages->contains(function ($i, $message) use ($email) {
            return $message->hasRecipient($email);
        });
    }

    public function lastMessage()
    {
        return $this->messages->last();
    }
}
