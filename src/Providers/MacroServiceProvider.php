<?php

namespace FluxErp\Providers;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MacroServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerArrMacros();
        $this->registerStrMacros();
        $this->registerRequestMacros();
        $this->registerCollectionMacros();
        $this->registerNumberMacros();
        $this->registerRouteMacros();
        $this->registerCommandMacros();
    }

    protected function registerArrMacros(): void
    {
        if (! Arr::hasMacro('sortByPattern')) {
            Arr::macro('sortByPattern', function (array $array, array $pattern) {
                $sortedAttributes = [];
                foreach ($pattern as $key) {
                    if (array_key_exists($key, $array)) {
                        $sortedAttributes[$key] = Arr::pull($array, $key);
                    }
                }

                // Merge the sorted attributes with the remaining attributes
                return array_merge($sortedAttributes, $array);
            });
        }

        if (! Arr::hasMacro('undotToTree')) {
            Arr::macro(
                'undotToTree',
                function (array $array, string $path = '', ?Closure $translate = null): array {
                    $array = Arr::undot($array);
                    $translate = $translate ?: fn ($key) => __(Str::headline($key));
                    $buildTree = function (array $array, string $path = '') use (&$buildTree, $translate) {
                        $tree = [];

                        foreach ($array as $key => $value) {
                            $currentPath = $path === '' ? $key : $path . '.' . $key;

                            if (is_array($value)) {
                                $tree[] = [
                                    'id' => $currentPath,
                                    'label' => $translate($key),
                                    'children' => $buildTree($value, $currentPath),
                                ];
                            } else {
                                $tree[] = [
                                    'id' => $currentPath,
                                    'label' => $translate($key),
                                    'value' => $value,
                                ];
                            }
                        }

                        return $tree;
                    };

                    return $buildTree($array, $path);
                }
            );
        }
    }

    protected function registerStrMacros(): void
    {
        if (! Str::hasMacro('iban')) {
            Str::macro('iban', function (?string $iban) {
                return trim(chunk_split($iban ?? '', 4, ' '));
            });
        }
    }

    protected function registerRequestMacros(): void
    {
        if (! Request::hasMacro('isPortal')) {
            Request::macro('isPortal', function () {
                // check if the current url matches with config('flux.portal_domain')
                // ignore http or https, just match the host itself
                return Str::startsWith($this->getHost(), Str::after(config('flux.portal_domain'), '://'));
            });
        }
    }

    protected function registerCollectionMacros(): void
    {
        if (! Collection::hasMacro('paginate')) {
            Collection::macro('paginate',
                function (int $perPage = 25, ?int $page = null, array $options = [], ?string $urlParams = null) {
                    $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

                    return (new LengthAwarePaginator(
                        $this->forPage($page, $perPage), $this->count(), $perPage, $page, $options))
                        ->withPath($urlParams ? dirname(url()->full()) . $urlParams : url()->full());
                });
        }
    }

    protected function registerNumberMacros(): void
    {
        if (! Number::hasMacro('fromFileSizeToBytes')) {
            Number::macro('fromFileSizeToBytes',
                function (string $size) {
                    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
                    preg_match('/^(\d+)([A-Z]{1,2})$/i', trim($size), $matches);

                    if (count($matches) !== 3) {
                        throw new InvalidArgumentException("Invalid size format: $size");
                    }

                    $numericPart = $matches[1];
                    $unit = strtoupper($matches[2]);

                    if (strlen($unit) === 1) {
                        $unit .= 'B';
                    }

                    $power = array_search($unit, $units);

                    if ($power === false) {
                        throw new InvalidArgumentException("Invalid size unit provided: $unit");
                    }

                    return bcmul($numericPart, bcpow('1024', $power), 0);
                });
        }
    }

    protected function registerRouteMacros(): void
    {
        Route::macro('getPermissionName',
            function () {
                $methods = array_flip($this->methods());
                Arr::forget($methods, 'HEAD');
                $method = array_keys($methods)[0];

                $uri = array_flip(array_filter(explode('/', $this->uri)));
                if (! $uri) {
                    return null;
                }

                $uri = array_keys($uri);
                $uri[] = $method;

                return strtolower(implode('.', $uri));
            }
        );

        Route::macro('hasPermission', function () {
            $this->setAction(array_merge($this->getAction(), [
                'permission' => route_to_permission($this, false),
            ]));

            return $this;
        });
    }

    protected function registerCommandMacros(): void
    {
        Command::macro('removeLastLine', function (): void {
            $this->output->write("\x1b[1A\r\x1b[K");
        });
    }
}
