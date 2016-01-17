<?php

namespace MailThief;

class Message
{
    public $view;
    public $data;
    public $to;
    public $subject;

    public function __construct($view, $data)
    {
        $this->view = $view;
        $this->data = $data;
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

    public function containsRecipient($email)
    {
        foreach ($this->to as $key => $value) {
            if ($email === $key || $email === $value) {
                return true;
            }
        }
        return false;
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
        throw new Exception("Method 'replyTo' is not implemented yet.");
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
