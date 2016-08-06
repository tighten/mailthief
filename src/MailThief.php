<?php

namespace MailThief;

use InvalidArgumentException;
use Illuminate\Support\Arr;
use Illuminate\Mail\MailableMailer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Mail\MailQueue;
use Illuminate\Queue\QueueManager;
use Illuminate\Contracts\Mail\Mailable as MailableContract;

class MailThief implements Mailer, MailQueue
{
    private $views;
    public $messages;
    public $later;

    protected $from;
    protected $to;
    protected $queue;
    
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
        $this->queue = app('queue');
    }

    public function raw($text, $callback)
    {
        $message = Message::fromRaw($text);
        $callback($message);
        $this->messages[] = $message;
    }

    public function send($view, array $data = [], $callback = null)
    {
        if ($view instanceof MailableContract) {
            return $view->send($this);
        }

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
                'raw'  => Arr::get($view, 'raw'),
            ]);
        }

        throw new InvalidArgumentException('Invalid view.');
    }

    public function failures()
    {
        // Impossible to detect failed recipients since no mail is actually sent.
        return [];
    }

    public function queue($view, array $data = [], $callback = null, $queue = null)
    {
        if ($view instanceof MailableContract) {
            return $view->queue($this->queue);
        }

        // @todo: Down the road it might be important to log the queue that is
        // meant to be used for people to assert against.
        return $this->send($view, $data, $callback);
    }

    public function later($delay, $view, array $data = [], $callback = null, $queue = null)
    {
        if ($view instanceof MailableContract) {
            return $view->later($delay, $this->queue);
        }

        $message = Message::fromView($view, $data);
        $message->delay = $delay;
        $callback($message);
        $this->later[] = $message;
    }

    public function alwaysFrom($address, $name = null)
    {
        $this->from = compact('address', 'name');
    }

    public function alwaysTo($address, $name = null)
    {
        $this->to = compact('address', 'name');
    }

    public function to($users)
    {
        return (new MailThiefMailable($this))->to($users);
    }

    public function bcc($users)
    {
        return (new MailThiefMailable($this))->bcc($users);
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

     /**
     * Set the queue manager instance.
     *
     * @param  \Illuminate\Contracts\Queue\Queue  $queue
     * @return $this
     */
    public function setQueue(QueueContract $queue)
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * Set the IoC container instance.
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return void
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

}
