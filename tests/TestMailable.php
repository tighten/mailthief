<?php

use Illuminate\Mail\Mailable;

class TestMailable extends Mailable
{
    public function build()
    {
        return $this->view('example-view');
    }
}
