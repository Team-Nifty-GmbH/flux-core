<?php

namespace FluxErp\Listeners\Order;

use Illuminate\Pipeline\Pipeline;

class PdfCreatedListener
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
     */
    public function handle(object $event): void
    {
        $pipeline = ($event->view::$pipelines)[get_class($event)] ?? [];

        if (! $pipeline) {
            return;
        }

        app(Pipeline::class)
            ->send($event)
            ->through($pipeline)
            ->thenReturn();
    }
}
