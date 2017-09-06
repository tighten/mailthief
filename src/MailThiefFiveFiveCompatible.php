<?php

namespace MailThief;

use Exception;
use Illuminate\Contracts\Mail\MailQueue;
use Illuminate\Contracts\Mail\Mailer;

class MailThiefFiveFiveCompatible extends MailThief implements Mailer, MailQueue
{
    public function onQueue($queue, $view, array $data, $callback)
    {
        throw new Exception('MailTheif doesn\'t support queueing in Laravel 5.5');
    }

    public function queueOn($queue, $view, array $data, $callback)
    {
        throw new Exception('MailTheif doesn\'t support queueing in Laravel 5.5');
    }

    public function laterOn($queue, $delay, $view, array $data, $callback)
    {
        throw new Exception('MailTheif doesn\'t support queueing in Laravel 5.5');
    }

    public function queue($view, $queue = null)
    {
        throw new Exception('MailTheif doesn\'t support queueing in Laravel 5.5');
    }

    public function later($delay, $view, $queue = null)
    {
        throw new Exception('MailTheif doesn\'t support queueing in Laravel 5.5');
    }

    public function to($users)
    {
        throw new Exception('MailTheif doesn\'t support mailables');
    }

    public function bcc($users)
    {
        throw new Exception('MailTheif doesn\'t support mailables');
    }
}
