<?php

namespace FluxErp\Menu;

use Closure;
use FluxErp\Models\Permission;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class MenuManager
{
    use Macroable;

    protected array $registered = [];

    protected array $registeredGroups = [];

    protected array $resolved = [];

    protected bool $isResolved = false;

    public function all(): array
    {
        $this->resolve();

        return $this->sortMultiDimensional($this->resolved);
    }

    public function clear(): void
    {
        $this->resolved = [];
        $this->registered = [];
        $this->registeredGroups = [];
        $this->isResolved = false;
    }

    public function forGuard(string $guard, ?string $group = null, bool $ignorePermissions = false): array
    {
        $this->resolve();
        $menuItems = $this->sortMultiDimensional(
            $this->resolved,
            function (array $value) use ($guard, $ignorePermissions) {
                if (($value['guard'] ?? $guard) !== $guard) {
                    return false;
                }

                $permission = data_get($value, 'permission');
                // first check if a permission exists
                if ($permission && ! $ignorePermissions) {
                    try {
                        resolve_static(
                            Permission::class,
                            'findByName',
                            [
                                'name' => $permission,
                                'guardName' => $guard,
                            ]
                        );
                    } catch (PermissionDoesNotExist) {
                        return true;
                    }

                    // if the user has the permission, return true
                    return auth()->user()?->can($permission);
                }

                return true;
            }
        );

        // filter out the group if it doesnt have children
        $menuItems = $group ? data_get($menuItems, $group, []) : $menuItems;

        return array_filter(
            $menuItems,
            fn ($value) => count(data_get($value, 'children', [])) > 0 || data_get($value, 'uri'),
        );
    }

    public function get(string $name): ?string
    {
        return $this->resolved[$name] ?? null;
    }

    public function group(
        string $path,
        ?string $icon = null,
        ?string $label = null,
        ?int $order = null,
        ?Closure $closure = null): void
    {
        data_set($this->registeredGroups, $path, [
            'label' => $label ?? data_get($this->registeredGroups, $path . '.label'),
            'icon' => $icon ?? data_get($this->registeredGroups, $path . '.icon'),
            'order' => $order ?? data_get($this->registeredGroups, $path . '.order'),
            'children' => data_get($this->registeredGroups, $path . '.children', []),
            'closure' => array_merge(data_get($this->registeredGroups, $path . '.closure', []), [$closure]),
        ]);
    }

    /**
     * @throws RouteNotFoundException
     */
    public function register(
        string|Route $route,
        ?string $icon = null,
        ?string $label = null,
        ?int $order = null,
        ?array $params = null,
        ?string $path = null
    ): void {
        $this->registered[] = [
            'route' => $route,
            'icon' => $icon,
            'label' => $label,
            'order' => $order,
            'params' => $params,
            'path' => $path,
        ];
    }

    public function unregister(string $name): void
    {
        unset($this->resolved[$name]);
    }

    protected function resolve(): void
    {
        if ($this->isResolved) {
            return;
        }

        foreach ($this->registeredGroups as $path => &$group) {
            data_set($this->resolved, $path, array_merge(
                data_get($this->resolved, $path) ?? [],
                [
                    'label' => $group['label'] ?? Str::afterLast($path, '.'),
                    'icon' => $group['icon'],
                    'order' => $group['order'],
                    'children' => [],
                ]
            ));

            if (count($group['closure'] ?? []) > 0) {
                foreach ($group['closure'] as $closure) {
                    $closure($this);
                }
                // Clear closures after execution to prevent re-execution
                $group['closure'] = [];
            }
        }
        unset($group); // Break the reference

        foreach ($this->registered as $item) {
            extract($item);
            $resolvedRoute = is_string($route)
                ? app('router')->getRoutes()->getByName($route)
                : $route;

            if (filter_var($route, FILTER_VALIDATE_URL)) {
                $resolvedRoute = (new Route(['GET'], $route, fn () => null))
                    ->setUri($route)
                    ->name($label ?? str($route)->slug());
            }

            if (! $resolvedRoute) {
                unset($this->registered[array_search($item, $this->registered)]);

                continue;
            }

            $routeName = $resolvedRoute->getName()
                ?? str($resolvedRoute->uri())->replace('/', '.')->toString();

            $guards = array_values(
                array_filter(
                    $resolvedRoute->middleware(),
                    fn ($guard) => str_starts_with($guard, 'auth:')
                )
            );
            $guard = array_shift($guards);
            $guard = str_replace('auth:', '', $guard);

            $path = data_get($item, 'path');
            $path ??= str_contains($routeName, '.') && ! str_ends_with($routeName, '.')
                ? Str::beforeLast($routeName, '.') . '.children.' . Str::afterLast($routeName, '.')
                : (str_ends_with($routeName, '.') ? rtrim($routeName, '.') : $routeName);

            data_set($this->resolved, $path, array_merge(
                data_get($this->resolved, $path) ?? [],
                [
                    'label' => $label ?? Str::afterLast($path, '.'),
                    'uri' => app('url')->toRoute($resolvedRoute, data_get($item, 'params', []), true),
                    'icon' => $icon,
                    'route_name' => $routeName,
                    'guard' => $guard ?: null,
                    'domain' => $resolvedRoute->getDomain(),
                    'permission' => $resolvedRoute->getPermissionName(),
                    'order' => $order
                        ?? count(
                            data_get($this->resolved, Str::beforeLast($path, '.'))
                                ?: (str_contains($path, '.') ? [] : $this->resolved)
                        ),
                ])
            );
        }

        $this->isResolved = true;
    }

    private function sortMultiDimensional(array $array, ?Closure $filter = null): array
    {
        $array = array_filter($array, $filter);

        $array = Arr::sort($array, function ($value) {
            return $value['order'] ?? 0;
        });

        foreach ($array as $key => $item) {
            if ($item['children'] ?? false) {
                data_set($array[$key], 'children', $this->sortMultiDimensional($item['children'], $filter));

                if (count($array[$key]) === 1) {
                    $array[$key] = $array[$key]['children'];
                }
            }
        }

        return array_filter($array);
    }
}
