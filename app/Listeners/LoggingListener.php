<?php

namespace App\Listeners;

use Illuminate\Log\Events\MessageLogged;

class LoggingListener
{
    /**
     * @var \Illuminate\Support\Collection
     */
    public $events;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->events = collect([]);
    }

    /**
     * Handle the event.
     *
     * @param MessageLogged $event
     * @return void
     */
    public function handle(MessageLogged $event)
    {
        $this->events->push($event);
    }
}
