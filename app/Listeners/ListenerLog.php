<?php
namespace ExactivEM\Listeners;
use Log;
use ExactivEM\User;
use Illuminate\Mail\Events\MessageSending;
class ListenerLog
{
    /**
    * Create the event listener.
    *
    * @return void
    */
    public function __construct()
    {
    //
    }

    /**
    * Handle the event.
    *
    * @param  PodcastWasPurchased  $event
    * @return void
    */
    public function handle(MessageSending $msg)
    {
        $to = $msg->message->getTo();
        $cc = $msg->message->getCc();
        $bcc = $msg->message->getBcc();
        $subject = $msg->message->getSubject();

        Log::info('Email sent.',[ 'to'=>$to, 'cc'=>$cc, 'bcc'=>$bcc, 'subject'=> $subject]);
    }
}