<?php

namespace MailThief;

use InvalidArgumentException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Mail\MailQueue;
use Illuminate\Contracts\View\Factory;

class MailThief implements Mailer, MailQueue
{
    private $views;
    public $messages;
    public $later;

    public function __construct(Factory $views)
    {
        $this->views = $views;
        $this->messages = collect();
        $this->later = collect();
    }

    public static function instance()
    {
        return app(MailThief::class);
    }

    public function hijack()
    {
        Mail::swap($this);
        app()->instance(Mailer::class, $this);

        if (config('mail.from.address')) {
            $this->alwaysFrom(config('mail.from.address'), config('mail.from.name'));
        }
    }

    public function raw($text, $callback)
    {
        $message = Message::fromRaw($text);
        $message = $this->prepareMessage($message, $callback);
        $this->messages[] = $message;
    }

    public function send($view, array $data = [], $callback = null)
    {
        $callback = $callback ?: null;
        $message = Message::fromView($this->renderViews($view, $data), $data);
        $message = $this->prepareMessage($message, $callback);
        $this->messages[] = $message;
    }

    private function renderViews($view, $data)
    {
        return collect($this->parseView($view))->map(function ($template, $part) use ($data) {
            if ($part == 'raw') {
                return $template;
            }

            return $this->views->make($template, $data)->render();
        })->all();
    }

    protected function parseView($view)
    {
        // Views passed as strings are treated as the HTML view
        if (is_string($view)) {
            return ['html' => $view];
        }

        // Numeric arrays are treated as [html, text]
        if (is_array($view) && isset($view[0])) {
            return ['html' => $view[0], 'text' => $view[1]];
        }

        // Non-numeric arrays are assumed to have named keys
        if (is_array($view)) {
            // Strip out empty views
            return array_filter([
                'html' => Arr::get($view, 'html'),
                'text' => Arr::get($view, 'text'),
                'raw' => Arr::get($view, 'raw'),
            ]);
        }

        throw new InvalidArgumentException('Invalid view.');
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

    public function onQueue($queue, $view, array $data, $callback)
    {
        return $this->queue($view, $data, $callback, $queue);
    }

    public function queueOn($queue, $view, array $data, $callback)
    {
        return $this->queue($view, $data, $callback, $queue);
    }

    public function later($delay, $view, array $data, $callback, $queue = null)
    {
        $message = Message::fromView($view, $data);
        $message->delay = $delay;
        $message = $this->prepareMessage($message, $callback);
        $this->later[] = $message;
    }

    public function laterOn($queue, $delay, $view, array $data, $callback)
    {
        return $this->later($delay, $view, $data, $callback, $queue);
    }

    public function hasMessageFor($email)
    {
        return $this->messages->contains(function (Message $message) use ($email) {
            return $message->hasRecipient($email);
        });
    }

    public function lastMessage()
    {
        return $this->messages->last();
    }

    public function prepareMessage($message, $callback)
    {
        if (! empty($this->from['address'])) {
            $message->from($this->from['address'], $this->from['name']);
        }

        $callback($message);

        return $message;
    }

    public function alwaysFrom($address, $name = null)
    {
        $this->from = ['address' => $address, 'name' => $name];
    }
}
