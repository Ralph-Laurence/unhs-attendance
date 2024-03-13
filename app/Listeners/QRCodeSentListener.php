<?php

namespace App\Listeners;

use App\Events\QRCodeSentEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class QRCodeSentListener
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
     * @param  \App\Events\QRCodeSentEvent  $event
     * @return void
     */
    public function handle(QRCodeSentEvent $event)
    {
        //
        // Delete the attachment
        if (file_exists($event->attachmentPath)) {
            // error_log('rem at ' . $event->attachmentPath);
            unlink($event->attachmentPath);
        }
    }
}
