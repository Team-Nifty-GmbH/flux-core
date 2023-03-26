<?php

namespace FluxErp\Listeners\Order;

use Illuminate\Pipeline\Pipeline;

class PdfCreatingListener
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
            ->send($event->model)
            ->through($pipeline)
            ->then(function ($model) use ($event) {
                $model->save();
                $event->model = $model;
            });
    }
}
