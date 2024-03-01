<?php

namespace FluxErp\Menu;

use FluxErp\Models\Permission;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class MenuManager
{
    use Macroable;

    protected array $menuItems = [];

    /**
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function register(
        Route $route,
        ?string $label = null,
        ?string $icon = null,
        ?int $order = null): void
    {
        $routeName = $route->getName();

        $guards = array_values(
            array_filter(
                $route->middleware(),
                fn ($guard) => str_starts_with($guard, 'auth:')
            )
        );
        $guard = array_shift($guards);
        $guard = str_replace('auth:', '', $guard);

        $path = str_contains($routeName, '.') && ! str_ends_with($routeName, '.')
            ? Str::beforeLast($routeName, '.') . '.children.' . Str::afterLast($routeName, '.')
            : (str_ends_with($routeName, '.') ? rtrim($routeName, '.') : $routeName);

        data_set($this->menuItems, $path, array_merge(
            data_get($this->menuItems, $path) ?? [],
            [
                'label' => $label ?: Str::afterLast($path, '.'),
                'uri' => Str::of($route->uri())->start('/')->toString(),
                'icon' => $icon,
                'route_name' => $routeName,
                'guard' => $guard,
                'domain' => $route->getDomain(),
                'permission' => $route->getPermissionName(),
                'order' => $order
                    ?? count(
                        data_get($this->menuItems, Str::beforeLast($path, '.'))
                            ?: (str_contains($path, '.') ? [] : $this->menuItems)
                    ),
            ])
        );
    }

    public function unregister(string $name): void
    {
        unset($this->menuItems[$name]);
    }

    public function all(): array
    {
        return $this->sortMultiDimensional($this->menuItems);
    }

    public function forGuard(string $guard, ?string $group = null, bool $ignorePermissions = false): array
    {
        $menuItems = $this->sortMultiDimensional(
            $this->menuItems,
            function (array $value) use ($guard, $ignorePermissions) {
                if (($value['guard'] ?? $guard) !== $guard) {
                    return false;
                }

                // first check if a permission exists
                if (($value['permission'] ?? false) && ! $ignorePermissions) {
                    try {
                        app(Permission::class)->findByName($value['permission'], $guard);
                    } catch (PermissionDoesNotExist) {
                        return true;
                    }

                    // if the user has the permission, return true
                    return auth()->user()?->can($value['permission']);
                }

                return true;
            }
        );

        return $group ? data_get($menuItems, $group, []) : $menuItems;
    }

    public function get(string $name): ?string
    {
        return $this->menuItems[$name] ?? null;
    }

    private function sortMultiDimensional(array $array, ?\Closure $filter = null): array
    {
        $array = array_filter($array, $filter);

        $array = Arr::sort($array, function ($value) {
            return $value['order'] ?? 0;
        });

        foreach ($array as $key => $item) {
            if ($item['children'] ?? false) {
                $array[$key]['children'] = $this->sortMultiDimensional($item['children'], $filter);

                if (count($array[$key]) === 1) {
                    $array[$key] = $array[$key]['children'];
                }
            }
        }

        return array_filter($array);
    }
}
