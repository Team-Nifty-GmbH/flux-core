<?php

namespace FluxErp\Console\Commands\Init;

use Closure;
use FluxErp\Facades\Action;
use FluxErp\Facades\Widget;
use FluxErp\Models\Permission;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Livewire\Mechanisms\ComponentRegistry;
use ReflectionClass;
use ReflectionFunction;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

use function Livewire\invade;

class InitPermissions extends Command
{
    private array $currentPermissions = [];

    protected $signature = 'init:permissions';

    protected $description = 'Creates a permission for every API route';

    public function handle(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->currentPermissions = array_flip(app(Permission::class)->all('id')->pluck('id')->toArray());

        $this->registerActionPermission();
        $this->registerRoutePermissions();
        $this->registerWidgetPermissions();
        $this->registerTabPermissions();

        resolve_static(Permission::class, 'query')->whereIntegerInRaw('id', array_keys($this->currentPermissions))->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
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
            if (! $guard) {
                continue;
            }

            $guard = str_replace('auth:', '', $guard);

            $permission = app(Permission::class)->findOrCreate($permissionName, $guard);

            unset($this->currentPermissions[$permission->id]);

            if ($guard[1] === $defaultGuard) {
                foreach (array_keys(config('auth.guards')) as $additionalGuard) {
                    if ($additionalGuard === $defaultGuard ||
                        config('auth.guards.' . $additionalGuard)['provider'] !== 'users') {
                        continue;
                    }

                    app(Permission::class)->findOrCreate($permissionName, $additionalGuard);
                }
            }

            $bar->advance();
        }

        $bar->finish();

        foreach (array_keys(config('auth.guards')) as $guard) {
            app(Role::class)->findOrCreate('Super Admin', $guard);
        }

        $this->newLine();
        $this->info('Permissions initiated!');
    }

    private function registerActionPermission(): void
    {
        $this->info('Registering action permissions…');
        foreach (Action::all() as $action) {
            if ($action['class']::hasPermission()) {
                $permission = app(Permission::class)->findOrCreate('action.' . $action['name'], 'web');
                unset($this->currentPermissions[$permission->id]);
            }
        }
    }

    private function registerWidgetPermissions(): void
    {
        $this->info('Registering widget permissions…');
        foreach (Widget::all() as $widget) {
            $permission = app(Permission::class)->findOrCreate(
                'widget.' . $widget['component_name'],
                'web'
            );
            unset($this->currentPermissions[$permission->id]);
        }
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
                $permission = app(Permission::class)->findOrCreate('tab.' . $tab->component, 'web');
                unset($this->currentPermissions[$permission->id]);
            }
        }
    }

    /**
     * @throws \ReflectionException
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
}
