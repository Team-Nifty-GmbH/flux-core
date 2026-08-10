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

        return collect([
            static::section($route, $alias),
            static::recordLabel($route, $alias),
            $appName,
        ])
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
        $parameter = collect($route->parameters())->first();

        $record = $parameter instanceof Model
            ? $parameter
            : ($alias && $parameter ? morph_to($alias, (int) $parameter) : null);

        return $record && method_exists($record, 'getLabel')
            ? $record->getLabel()
            : null;
    }
}
