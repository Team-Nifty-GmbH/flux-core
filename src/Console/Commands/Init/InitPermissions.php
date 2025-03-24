<?php

namespace FluxErp\Console\Commands\Init;

use Closure;
use FluxErp\Actions\FluxAction;
use FluxErp\Facades\Action;
use FluxErp\Facades\Widget;
use FluxErp\Models\Permission;
use FluxErp\Traits\HasModelPermission;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Livewire\Mechanisms\ComponentRegistry;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use function Livewire\invade;

class InitPermissions extends Command
{
    protected $description = 'Creates a permission for every API route';

    protected $signature = 'init:permissions';

    private array $currentPermissions = [];

    public function handle(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->currentPermissions = array_flip(
            resolve_static(Permission::class, 'query')
                ->pluck('id')
                ->toArray()
        );

        $this->registerActionPermission();
        $this->registerActionPermission('sanctum');
        $this->registerModelGetPermission();
        $this->registerRoutePermissions();
        $this->registerWidgetPermissions();
        $this->registerTabPermissions();

        resolve_static(Permission::class, 'query')
            ->whereIntegerInRaw('id', array_keys($this->currentPermissions))
            ->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function registerTabPermissions(): void
    {
        $this->info('Registering tab permissions…');
        $registry = app(ComponentRegistry::class);
        foreach (invade($registry)->aliases as $component) {
            if (! in_array(WithTabs::class, class_uses_recursive($component))) {
                continue;
            }

            $componentInstance = new $component();

            foreach ($componentInstance->renderingWithTabs()->getTabsToRender() as $tab) {
                $permission = resolve_static(
                    Permission::class,
                    'findOrCreate',
                    [
                        'name' => 'tab.' . $tab->component,
                        'guardName' => 'web',
                    ]
                );
                unset($this->currentPermissions[$permission->id]);
            }
        }
    }

    /**
     * Determine if the route uses a framework controller.
     */
    protected function isFrameworkController(\Illuminate\Routing\Route $route): bool
    {
        return in_array($route->getControllerClass(), [
            '\Illuminate\Routing\RedirectController',
            '\Illuminate\Routing\ViewController',
        ], true);
    }

    /**
     * @throws ReflectionException
     */
    protected function isVendorRoute(\Illuminate\Routing\Route $route): bool
    {
        if (array_search('permission', $route->getAction('middleware'))) {
            return false;
        }

        if ($route->action['uses'] instanceof Closure) {
            $path = (new ReflectionFunction($route->action['uses']))
                ->getFileName();
        } elseif (is_string($route->action['uses']) &&
            str_contains($route->action['uses'], 'SerializableClosure')) {
            return false;
        } elseif (is_string($route->action['uses'])) {
            if ($this->isFrameworkController($route)) {
                return false;
            }

            $path = (new ReflectionClass($route->getControllerClass()))
                ->getFileName();
        } else {
            return false;
        }

        return str_starts_with($path, base_path('vendor'))
            && ! str_starts_with($path, base_path('vendor/team-nifty-gmbh/flux-erp'));
    }

    private function registerActionPermission(string $guardName = 'web'): void
    {
        $this->info('Registering action permissions for guard ' . $guardName . '…');
        foreach (Action::all() as $action) {
            if ($action['class']::hasPermission()) {
                $permission = resolve_static(
                    Permission::class,
                    'findOrCreate',
                    [
                        'name' => 'action.' . $action['name'],
                        'guardName' => $guardName,
                    ]
                );
                unset($this->currentPermissions[$permission->id]);
            }
        }
    }

    private function registerModelGetPermission(): void
    {
        $this->info('Registering model get permissions…');
        $roles = resolve_static(Role::class, 'query')
            ->where('guard_name', 'web')
            ->get();
        $newPermissions = [];

        foreach (Relation::morphMap() as $alias => $class) {
            if (in_array(HasModelPermission::class, class_uses_recursive($class))
                && resolve_static($class, 'hasPermission')
            ) {
                $permission = resolve_static(
                    Permission::class,
                    'findOrCreate',
                    [
                        'name' => 'model.' . $alias . '.get',
                        'guardName' => 'web',
                    ]
                );

                if (is_null(data_get($this->currentPermissions, $permission->id))) {
                    $newPermissions[] = $permission->getKey();
                }

                unset($this->currentPermissions[$permission->id]);
            }
        }

        if ($newPermissions) {
            foreach ($roles as $role) {
                /** @var Role $role */
                $role->givePermissionTo($newPermissions);
            }
        }
    }

    private function registerRoutePermissions(): void
    {
        $guards = array_keys(Arr::prependKeysWith(config('auth.guards'), 'auth:'));
        $guards[] = 'auth';
        $defaultGuard = config('auth.defaults.guard');

        $routes = Route::getRoutes()->getRoutes();

        $this->info('Registering route permissions…');
        $bar = $this->output->createProgressBar(count($routes));
        foreach ($routes as $route) {
            $permissionName = route_to_permission($route, false);
            if (! $permissionName || $this->isVendorRoute($route) || str_starts_with($permissionName, 'search.')) {
                $bar->advance();

                continue;
            }

            $guards = array_values(array_filter($route->middleware(), fn ($guard) => str_starts_with($guard, 'auth:')));
            $guard = array_shift($guards);

            // omit api routes
            if (! $guard || is_a($route->getAction('controller'), FluxAction::class, true)) {
                continue;
            }

            $guard = str_replace('auth:', '', $guard);

            $permission = resolve_static(
                Permission::class,
                'findOrCreate',
                [
                    'name' => $permissionName,
                    'guardName' => $guard,
                ]
            );

            unset($this->currentPermissions[$permission->id]);

            if ($guard[1] === $defaultGuard) {
                foreach (array_keys(config('auth.guards')) as $additionalGuard) {
                    if ($additionalGuard === $defaultGuard ||
                        config('auth.guards.' . $additionalGuard)['provider'] !== 'users') {
                        continue;
                    }

                    resolve_static(
                        Permission::class,
                        'findOrCreate',
                        [
                            'name' => $permissionName,
                            'guardName' => $additionalGuard,
                        ]
                    );
                }
            }

            $bar->advance();
        }

        $bar->finish();

        foreach (array_keys(config('auth.guards')) as $guard) {
            resolve_static(
                Role::class,
                'findOrCreate',
                [
                    'name' => 'Super Admin',
                    'guardName' => $guard,
                ]
            );
        }

        $this->newLine();
        $this->info('Permissions initiated!');
    }

    private function registerWidgetPermissions(): void
    {
        $this->info('Registering widget permissions…');
        foreach (Widget::all() as $widget) {
            $permission = resolve_static(
                Permission::class,
                'findOrCreate',
                [
                    'name' => 'widget.' . $widget['component_name'],
                    'guardName' => 'web',
                ]
            );
            unset($this->currentPermissions[$permission->id]);
        }
    }
}
