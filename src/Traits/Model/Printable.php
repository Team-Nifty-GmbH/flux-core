<?php

namespace FluxErp\Traits\Model;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

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
        $user = Auth::user();

        $printViews = array_merge(
            array_filter(
                $this->getPrintViews(),
                function (string|int $key) use ($user) {
                    if ($user?->hasRole('Super Admin')) {
                        return true;
                    }

                    try {
                        return $user
                            ?->hasPermissionTo(print_view_to_permission($key, $this->getMorphClass()))
                            ?? true;
                    } catch (PermissionDoesNotExist) {
                        return true;
                    }
                },
                ARRAY_FILTER_USE_KEY
            ),
            static::$registeredPrintViews
        );

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
