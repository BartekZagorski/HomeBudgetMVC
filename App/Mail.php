<?php

namespace App;

use Mailgun\Mailgun;
use \App\Config;

class Mail
{
    public static function send($to, $subject, $text, $html)
    {
            // First, instantiate the SDK with your API credentials
            $mg = Mailgun::create(Config::MAILGUN_API_KEY); // For US servers
            //$mg = Mailgun::create('key-example', 'https://api.eu.mailgun.net'); // For EU servers

            // Now, compose and send your message.
            // $mg->messages()->send($domain, $params);
            $mg->messages()->send(Config::MAILGUN_DOMAIN, [
            'from'    => 'homebudget@homebudget.com',
            'to'      => $to,
            'subject' => $subject,
            'text'	=> $text,
            'html'  => $html
            ]);        
    }
}   