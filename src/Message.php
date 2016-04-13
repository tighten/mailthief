<?php

namespace MailThief;

use Exception;

class Message
{
    private $views;

    public $view;
    public $data;
    public $to;
    public $subject;
    public $reply_to;

    public function __construct($view, $data, $views)
    {
        $this->view = $view;
        $this->data = $data;
        $this->views = $views;
        $this->to = collect();
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

    public function hasRecipient($email)
    {
        return $this->to->has($email) || $this->to->contains($email);
    }

    public function contains($text)
    {
        return str_contains($this->getBody(), $text);
    }

    public function getBody()
    {
        return $this->views->make($this->view, $this->data)->render();
    }

    // @todo
    public function from($address, $name = null)
    {
        throw new Exception("Method 'from' is not implemented yet.");
    }

    public function sender($address, $name = null)
    {
        throw new Exception("Method 'sender' is not implemented yet.");
    }

    public function cc($address, $name = null)
    {
        throw new Exception("Method 'cc' is not implemented yet.");
    }

    public function bcc($address, $name = null)
    {
        throw new Exception("Method 'bcc' is not implemented yet.");
    }

    public function replyTo($address, $name = null)
    {
        $this->reply_to = $address;
    }

    public function priority($level)
    {
        throw new Exception("Method 'priority' is not implemented yet.");
    }

    public function attach($pathToFile, array $options = [])
    {
        throw new Exception("Method 'attach' is not implemented yet.");
    }

    public function attachData($data, $name, array $options = [])
    {
        throw new Exception("Method 'attachData' is not implemented yet.");
    }

    public function getSwiftMessage()
    {
        throw new Exception("Method 'getSwiftMessage' is not implemented yet.");
    }
}
