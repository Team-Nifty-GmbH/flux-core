<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Services\PrintService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Http\Client\ClientExceptionInterface;

class PrintController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * A get route that returns the printable view for an already saved model
     *
     *
     * @throws ClientExceptionInterface
     */
    public function render(Request $request, string $view, string $model, string $id, bool $asPdf = false): View|Factory|Response
    {
        $model = qualify_model($model);

        if ($asPdf) {
            return $this->renderPdf($view, $model, $id);
        }

        return (new PrintService())->render($view, $model, $id);
    }

    /**
     * Returns the given route as a pdf.
     * The Route has to be reachable from the gotenberg docker container.
     * Thats why localhost is not allowed here. Change your APP_URL env key to the ip of your docker host.
     *
     * @param string $route
     *
     * @throws ClientExceptionInterface
     */
    public function renderPdf(string $view, string $model, string $id): mixed
    {
        $pdfResponse = (new PrintService())->viewToPdf($view, $model, $id);

        return response()->attachment($pdfResponse);
    }
}
