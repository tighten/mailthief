<?php

namespace MailThief;

use Illuminate\Contracts\Mail\MailQueue;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

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
    }

    public function raw($text, $callback)
    {
        $message = Message::fromRaw($text);
        $callback($message);
        $this->messages[] = $message;
    }

    public function send($view, array $data = [], $callback = null)
    {
        $callback = $callback ?: null;
        $message = Message::fromView($this->renderViews($view, $data), $data);
        $callback($message);
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

    public function later($delay, $view, array $data, $callback, $queue = null)
    {
        $message = Message::fromView($view, $data);
        $message->delay = $delay;
        $callback($message);
        $this->later[] = $message;
    }

    public function hasMessageFor($email)
    {
        if (str_contains(app()::VERSION, '5.3')) {
            return $this->messages->contains(function (Message $message) use ($email) {
                return $message->hasRecipient($email);
            });
        } else {
            return $this->messages->contains(function ($i, Message $message) use ($email) {
                return $message->hasRecipient($email);
            });
        }
    }

    public function lastMessage()
    {
        return $this->messages->last();
    }
}
