<?php

namespace MailThief;

use Illuminate\Contracts\Mail\MailQueue;
use Illuminate\Contracts\Mail\Mailer;

class MailThiefFiveFourCompatible extends MailThief implements Mailer, MailQueue
{
    public function onQueue($queue, $view, array $data, $callback)
    {
        return $this->queue($view, $data, $callback, $queue);
    }

    public function queueOn($queue, $view, array $data, $callback)
    {
        return $this->queue($view, $data, $callback, $queue);
    }

    public function laterOn($queue, $delay, $view, array $data, $callback)
    {
        return $this->later($delay, $view, $data, $callback, $queue);
    }

    public function queue($view, array $data, $callback, $queue = null)
    {
        // @todo: Down the road it might be important to log the queue that is
        // meant to be used for people to assert against.
        return $this->send($view, $data, $callback);
    }

    public function later($delay, $view, array $data, $callback, $queue = null)
    {
        $message = Message::fromView($view, $data);
        $message->delay = $delay;
        $this->prepareMessage($message, $callback);
        $this->later[] = $message;
    }
}
