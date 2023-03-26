<?php

namespace FluxErp\Events\Print;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PdfCreatingEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Model $model;

    public string $view;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Model $model, string $view)
    {
        $this->view = $view;
        $this->model = $model;
    }
}
