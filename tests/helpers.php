<?php

if (! function_exists('config')) {
    /**
     * This function is a mocked version from the one
     * implemented in the Laravel framework, 
     *
     * This version will return the same values for the
     * same keys always.
     */
    function config($key = null)
    {
        $config = [
            'mail.from.name' => 'First Last',
            'mail.from.address' => 'foo@bar.tld',
        ];

        if( isset($config[$key]) ){
            return $config[$key];
        }

        return null;
    }
}