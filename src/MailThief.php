<?php

namespace MailThief;

use Illuminate\Contracts\Mail\MailQueue;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\View\Factory;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use ReflectionMethod;
use InvalidArgumentException;
use MailThief\Support\MailThiefCollection;

class MailThief implements Mailer, MailQueue
{
    private $views;
    public $messages;
    public $later;

    public function __construct(Factory $views, ConfigRepository $config)
    {
        $this->views = $views;
        $this->config = $config;
        $this->messages = new MailThiefCollection;
        $this->later = new MailThiefCollection;
    }

    public static function instance()
    {
        return app(MailThief::class);
    }

    public function hijack()
    {
        $this->swapMail();
        $this->loadGlobalFrom();
    }

    protected function swapMail()
    {
        Mail::swap($this);
        app()->instance(Mailer::class, $this);
    }

    protected function loadGlobalFrom()
    {
        if ($this->config->has('mail.from.address')) {
            $this->alwaysFrom($this->config->get('mail.from.address'), $this->config->get('mail.from.name'));
        }
    }

    public function to($users)
    {
        return (new MailThiefPendingMail($this))->to($users);
    }

    public function bcc($users)
    {
        return (new MailThiefPendingMail($this))->bcc($users);
    }

    public function raw($text, $callback)
    {
        $message = Message::fromRaw($text);
        $this->prepareMessage($message, $callback);
        $this->messages[] = $message;
    }

    public function send($view, array $data = [], $callback = null)
    {
        if ($view instanceof MailableContract) {
            return $this->sendMailable($view);
        }

        $callback = $callback ?: null;

        $data['message'] = new NullMessageForView;

        $message = Message::fromView($this->renderViews($view, $data), $data);

        $this->prepareMessage($message, $callback);

        $this->messages[] = $message;
    }

    protected function sendMailable(Mailable $mailable)
    {
        return $mailable->send($this);
    }

    private function renderViews($view, $data)
    {
        return collect($this->parseView($view))->map(function ($template, $part) use ($data) {
            if ($part == 'raw') {
                return $template;
            }

            return $template instanceof HtmlString
                ? $template->toHtml()
                : $this->views->make($template, $data)->render();
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

    public function queue($view, $queue = null)
    {
        if (! $view instanceof MailableContract) {
            throw new InvalidArgumentException('Only mailables may be queued.');
        }

        return $this->send($view);
    }

    public function onQueue($queue, $view)
    {
        return $this->queue($view, $queue);
    }

    public function queueOn($queue, $view)
    {
        return $this->queue($view, $queue);
    }

    public function later($delay, $view, $queue = null)
    {
        if (! $view instanceof MailableContract) {
            throw new InvalidArgumentException('Only mailables may be queued.');
        }

        $view->build();

        $data = $view->buildViewData();
        $data['message'] = new NullMessageForView;

        $buildView = new ReflectionMethod(Mailable::class, 'buildView');
        $buildView->setAccessible(true);

        $message = Message::fromView($this->renderViews($buildView->invoke($view), $data), $data);
        $message->delay = $delay;
        $this->prepareMessage($message, function ($message) use ($view) {
            $buildFrom = new ReflectionMethod(Mailable::class, 'buildFrom');
            $buildFrom->setAccessible(true);
            $buildFrom->invoke($view, $message);

            $buildRecipients = new ReflectionMethod(Mailable::class, 'buildRecipients');
            $buildRecipients->setAccessible(true);
            $buildRecipients->invoke($view, $message);

            $buildSubject = new ReflectionMethod(Mailable::class, 'buildSubject');
            $buildSubject->setAccessible(true);
            $buildSubject->invoke($view, $message);

            $buildAttachments = new ReflectionMethod(Mailable::class, 'buildAttachments');
            $buildAttachments->setAccessible(true);
            $buildAttachments->invoke($view, $message);

            $runCallbacks = new ReflectionMethod(Mailable::class, 'runCallbacks');
            $runCallbacks->setAccessible(true);
            $runCallbacks->invoke($view, $message);
        });
        $this->later[] = $message;
    }

    public function laterOn($queue, $delay, $view)
    {
        return $this->later($delay, $view, $queue);
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

    public function subjects()
    {
        return $this->messages->pluck('subject');
    }
}
