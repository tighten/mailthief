<?php 
namespace MailThief;

class MailThiefMailable extends \Illuminate\Mail\MailableMailer
{
    /**
     * Create a new mailable mailer instance.
     *
     * @param  MailThief  $mailer
     * @return void
     */
    public function __construct(MailThief $mailer)
    {
        $this->mailer = $mailer;
    }
}