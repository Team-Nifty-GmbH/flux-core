<?php

namespace FluxErp\View;

use Illuminate\Database\Eloquent\Model;
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

        $alias = $route->getMetadata('model');
        $section = static::section($route, $alias);
        $record = static::recordLabel($route, $alias);

        if ($section && $record && str_starts_with($record, $section)) {
            $section = null;
        }

        return collect([$section, $record, $appName])
            ->filter()
            ->implode(' / ');
    }

    protected static function section(Route $route, ?string $alias): ?string
    {
        $section = $route->getMetadata('title')
            ?: class_basename($alias ? morphed_model($alias) ?? '' : '')
            ?: Str::afterLast(rtrim($route->getName() ?? '', '.'), '.');

        return $section ? __(Str::headline($section)) : null;
    }

    protected static function recordLabel(Route $route, ?string $alias): ?string
    {
        $parameter = $route->hasParameters()
            ? collect($route->parameters())->first()
            : null;

        $record = $parameter instanceof Model
            ? $parameter
            : ($alias && $parameter ? morph_to($alias, (int) $parameter) : null);

        return $record && method_exists($record, 'getLabel')
            ? $record->getLabel()
            : null;
    }
}
