<?php

namespace FluxErp\View\Layouts;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class App extends Component
{
    public function __construct(public ?string $title = null)
    {
        $this->title ??= $this->titleForRoute(Request::route());
    }

    public function render(): View|Closure|string
    {
        return view('flux::layouts.app');
    }

    protected function titleForRoute(?Route $route): string
    {
        $appName = config('app.name', 'Flux ERP');

        if (! $route) {
            return $appName;
        }

        return collect([
            $this->section($route),
            $this->recordLabel($route),
            $appName,
        ])
            ->filter()
            ->implode(' / ');
    }

    protected function section(Route $route): ?string
    {
        $section = $route->getMetadata('title');

        if (! $section && $model = $this->modelForRoute($route)) {
            $section = class_basename($model);
        }

        $section ??= Str::afterLast(rtrim($route->getName() ?? '', '.'), '.');

        return $section ? __(Str::headline($section)) : null;
    }

    protected function recordLabel(Route $route): ?string
    {
        $model = $this->modelForRoute($route);

        if (! $model || ! $key = $route->parameter('id')) {
            return null;
        }

        $record = resolve_static($model, 'query')->whereKey($key)->first();

        return $record && method_exists($record, 'getLabel') ? $record->getLabel() : null;
    }

    protected function modelForRoute(Route $route): ?string
    {
        $model = $route->getMetadata('model');

        return $model
            ? Relation::getMorphedModel($model) ?? $model
            : null;
    }
}
