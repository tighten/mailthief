<?php

namespace MailThief;

use Exception;
use Illuminate\Support\Arr;

class Message
{
    private $view;

    public $data;
    public $subject;
    public $from;
    public $sender;
    public $to;
    public $cc;
    public $bcc;
    public $reply_to;
    public $priority;
    public $attachments;
    public $headers;
    public $delay = 0;
    /**
     * Methods that are available in Laravel but not provided by MailThief
     * @var array
     */
    public $valid_methods = [
        'addPart',
        'getHeaders',
        'setReadReceiptTo',
        'setCharset',
        'setMaxLineLength',
        'attachSigner',
    ];

    public function __construct($view, $data)
    {
        $this->view = $view;
        $this->data = $data;
        $this->to = collect();
        $this->cc = collect();
        $this->bcc = collect();
        $this->reply_to = collect();
        $this->attachments = collect();
        $this->headers = collect();
    }

    public function __call($name, $arguments)
    {
        if (in_array(strtolower($name), array_map('strtolower', $this->valid_methods))) {
            return $this;
        }

        throw new Exception("Invalid method ($name) called");
    }

    public static function fromView($view, $data)
    {
        return new self($view, $data);
    }

    public static function fromRaw($body)
    {
        return new self(['raw' => $body], []);
    }

    public function getBody($part = 'html')
    {
        return Arr::get($this->view, $part, reset($this->view));
    }

    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function to($address, $name = null)
    {
        if (! is_array($address)) {
            $address = $name ? [$address => $name] : [$address];
        }

        $this->to = $this->to->merge($address);

        return $this;
    }

    public function cc($address, $name = null)
    {
        if (! is_array($address)) {
            $address = $name ? [$address => $name] : [$address];
        }

        $this->cc = $this->cc->merge($address);

        return $this;
    }

    public function bcc($address, $name = null)
    {
        if (! is_array($address)) {
            $address = $name ? [$address => $name] : [$address];
        }

        $this->bcc = $this->bcc->merge($address);

        return $this;
    }

    public function replyTo($address, $name = null)
    {
        if (! is_array($address)) {
            $address = $name ? [$address => $name] : [$address];
        }

        $this->reply_to = $this->reply_to->merge($address);

        return $this;
    }

    public function from($address, $name = null)
    {
        if (! is_array($address)) {
            $address = $name ? [$address => $name] : [$address];
        }

        $this->from = collect($address);

        return $this;
    }

    public function sender($address, $name = null)
    {
        if (! is_array($address)) {
            $address = $name ? [$address => $name] : [$address];
        }

        $this->sender = collect($address);

        return $this;
    }

    public function priority($level)
    {
        $this->priority = $level;
        return $this;
    }

    public function hasRecipient($email)
    {
        return $this->recipients()->has($email) || $this->recipients()->contains($email);
    }

    public function recipients()
    {
        return $this->to->merge($this->cc)->merge($this->bcc);
    }

    public function contains($text, $part = 'html')
    {
        return str_contains($this->getBody($part), $text);
    }

    public function attach($pathToFile, array $options = [])
    {
        $this->attachments[] = ['path' => $pathToFile, 'options' => $options];
        return $this;
    }

    public function attachData($data, $name, array $options = [])
    {
        $this->attachments[] = ['data' => $data, 'name' => $name, 'options' => $options];
        return $this;
    }

    public function addTextHeader($name, $value = null)
    {
        $this->headers[] = ['name' => $name, 'value' => $value];
        return $this;
    }

    public function getSwiftMessage()
    {
        throw new Exception("Cannot get Swift message from MailThief message.");
    }
}
