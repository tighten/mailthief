<?php

namespace MailThief;

use Illuminate\Mail\PendingMail;

class MailThiefPendingMail extends PendingMail
{
    public function __construct(MailThief $mailer)
    {
        $this->mailer = $mailer;
    }
}
