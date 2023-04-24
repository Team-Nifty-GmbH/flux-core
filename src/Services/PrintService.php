<?php

namespace FluxErp\Services;

use FluxErp\Events\Print\PdfCreatedEvent;
use FluxErp\Events\Print\PdfCreatingEvent;
use FluxErp\Helpers\ResponseHelper;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PrintService
{
    /**
     * Get print layouts and templates for a given path
     */
    public function getPrintViews(string $namespace = null, string $name = null): array
    {
        $namespace = str_replace('/', '\\', $namespace);

        $availableViews = config('print.views');

        $classViews = array_filter($availableViews, function ($key) use ($namespace) {
            return Str::startsWith(strtolower($key), strtolower($namespace));
        }, ARRAY_FILTER_USE_KEY);

        $response = [];
        foreach ($classViews as $class => $views) {
            foreach ($views as $key => $view) {
                $response[] = [
                    'name' => is_string($key) ? $key : strtolower(class_basename($view)),
                    'view' => $view,
                    'model' => $class,
                ];
            }
        }

        if ($name) {
            $response = array_filter($response, function ($item) use ($name) {
                return strtolower($item['name']) === strtolower($name);
            });
        }

        return ResponseHelper::createArrayResponse(statusCode: 200, data: $response);
    }

    public function render(string $view, string $model, string $id): View|Factory
    {
        $views = $this->parseView($model, $view);

        $viewClass = $views['view'];
        $modelClass = $views['model'];

        $model = $modelClass::query()->whereKey($id)->firstOrFail();

        return (new $viewClass($model))->render();
    }

    public function viewToPdf(string $view, string $model, string $id): PromiseInterface|Response
    {
        $views = $this->parseView($model, $view);
        $modelInstance = $views['model']::query()->whereKey($id)->firstOrFail();

        PdfCreatingEvent::dispatch($modelInstance, $views['view']);

        $route = route('print.render', [$view, $model, $id]);

        $url = config('flux.gotenberg.host') . ':' . config('flux.gotenberg.port');
        $endpoint = '/forms/chromium/convert/url';

        $response = Http::asMultipart()
            ->post($url . $endpoint, [
                'url' => $route,
                'preferCssPageSize' => true,
                'printBackground' => true,
                'marginTop' => 0,
                'marginBottom' => 0,
                'marginLeft' => 0,
                'marginRight' => 0,
            ]);

        if ($response->status() > 200) {
            abort($response->status(), $response->body());
        }

        PdfCreatedEvent::dispatch($modelInstance, $response, $views['view']);

        return $response;
    }

    private function parseView(string $model, string $view): array
    {
        $views = $this->getPrintViews($model, $view)['data'];

        if (count($views) !== 1) {
            abort(404, count($views) < 1 ? __('View not found') : __('Multiple views found'));
        }

        return array_values($views)[0];
    }
}
