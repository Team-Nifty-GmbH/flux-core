<?php

namespace FluxErp\Traits;

use Closure;

trait Printable
{
    public static array $registeredPrintViews = [];

    public static function registerPrintView(string $name, Closure|string $viewClass): void
    {
        static::$registeredPrintViews[$name] = $viewClass;
    }

    public function getAvailableViews(): array
    {
        return array_keys(array_merge($this->getPrintViews(), static::$registeredPrintViews));
    }

    public function getPrintViews(): array
    {
        return [];
    }

    public function print(): \FluxErp\Printing\Printable
    {
        return new \FluxErp\Printing\Printable($this);
    }

    public function resolvePrintViews(): array
    {
        $printViews = array_merge($this->getPrintViews(), static::$registeredPrintViews);

        foreach ($printViews as $name => $view) {
            if (is_string($view)) {
                continue;
            }

            if ($view instanceof Closure) {
                $resolvedClosure = $view($this);
                $printViews[$name] = $resolvedClosure ?: null;
            }
        }

        return array_filter($printViews);
    }
}
