<?php

namespace FluxErp\Console\Commands\Init;

use Closure;
use FluxErp\Facades\Action;
use FluxErp\Facades\Widget;
use FluxErp\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use ReflectionFunction;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class InitPermissions extends Command
{
    private array $currentPermissions = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a permission for every API route';

    /**
     * Execute the console command.
     *
     *
     * @throws \ReflectionException
     */
    public function handle(): int
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->currentPermissions = array_flip(Permission::all('id')->pluck('id')->toArray());

        $this->registerActionPermission();
        $this->registerRoutePermissions();
        $this->registerWidgetPermissions();

        foreach ($this->currentPermissions as $id => $currentPermission) {
            Permission::query()->whereKey($id)->delete();
        }

        return 0;
    }

    private function registerRoutePermissions(): void
    {
        $guards = array_keys(Arr::prependKeysWith(config('auth.guards'), 'auth:'));
        $guards[] = 'auth';
        $defaultGuard = config('auth.defaults.guard');

        $routes = Route::getRoutes()->getRoutes();

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

            $permission = Permission::findOrCreate($permissionName, $guard);

            unset($this->currentPermissions[$permission->id]);

            if ($guard[1] === $defaultGuard) {
                foreach (array_keys(config('auth.guards')) as $additionalGuard) {
                    if ($additionalGuard === $defaultGuard ||
                        config('auth.guards.' . $additionalGuard)['provider'] !== 'users') {
                        continue;
                    }

                    Permission::findOrCreate($permissionName, $additionalGuard);
                }
            }

            $bar->advance();
        }

        $bar->finish();

        Role::findOrCreate('Super Admin');

        $this->newLine();
        $this->info('Permissions initiated!');
    }

    private function registerActionPermission(): void
    {
        $this->info('Registering action permissions');
        foreach (Action::all() as $action) {
            $permission = Permission::findOrCreate('action.' . $action['name'], 'web');
            unset($this->currentPermissions[$permission->id]);
        }
    }

    private function registerWidgetPermissions(): void
    {
        $this->info('Registering widget permissions');
        foreach (Widget::all() as $widget) {
            $permission = Permission::findOrCreate('widget.' . $widget['name'], 'web');
            unset($this->currentPermissions[$permission->id]);
        }
    }

    /**
     * @throws \ReflectionException
     */
    protected function isVendorRoute(\Illuminate\Routing\Route $route): bool
    {
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
