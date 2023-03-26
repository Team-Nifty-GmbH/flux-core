<?php

namespace FluxErp\Events\Print;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\SerializesModels;

class PdfCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Model $model;

    public $response;

    public string $view;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Model $model, PromiseInterface|Response $response, string $view)
    {
        $this->model = $model;
        $this->response = $response;
        $this->view = $view;
    }
}
