<?php

namespace FluxErp\View;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class PageTitle
{
    public static function forRoute(?Route $route): string
    {
        $appName = config('app.name', 'Flux ERP');

        if (! $route) {
            return $appName;
        }

        $model = static::model($route);
        $section = $route->getMetadata('title')
            ?: ($model
                ? class_basename($model)
                : Str::afterLast(rtrim($route->getName() ?? '', '.'), '.'));

        return collect([
            $section ? __(Str::headline($section)) : null,
            static::record($route, $model),
            $appName,
        ])
            ->filter()
            ->implode(' / ');
    }

    protected static function model(Route $route): ?string
    {
        $model = $route->getMetadata('model');

        return $model
            ? Relation::getMorphedModel($model) ?? $model
            : null;
    }

    protected static function record(Route $route, ?string $model): ?string
    {
        if (! $model || ! $key = $route->parameter('id')) {
            return null;
        }

        $record = resolve_static($model, 'query')->whereKey($key)->first();

        return $record && method_exists($record, 'getLabel') ? $record->getLabel() : null;
    }
}
