<?php

namespace MailThief;

use Exception;

class NullMessageForView
{
    public function __call($method, $params)
    {
        if (in_array($method, ['embed', 'embedData'])) {
            return '';
        }

        throw new Exception("MailThief message unable to respond to method call: [${$method}]");
    }
}
