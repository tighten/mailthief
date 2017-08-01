<?php

namespace MailThief;

use Exception;

class NullMessageForView
{
    protected $validMethods = [
        'embed',
        'embedData',
    ];

    public function __call($method, $params)
    {
        if (in_array($method, $this->validMethods)) {
            return $this;
        }

        throw new Exception("MailThief message unable to respond to method call: [${$method}]");
    }
}
