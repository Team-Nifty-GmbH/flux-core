<?php

namespace FluxErp\Console\Commands\Init;

use Closure;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use ReflectionFunction;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class InitPermissions extends Command
{
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
        $guards = array_keys(Arr::prependKeysWith(config('auth.guards'), 'auth:'));
        $guards[] = 'auth';
        $defaultGuard = config('auth.defaults.guard');

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $routes = Route::getRoutes()->getRoutes();
        $currentPermissions = array_flip(Permission::all('id')->pluck('id')->toArray());

        $bar = $this->output->createProgressBar(count($routes));
        foreach ($routes as $route) {
            $permissionName = route_to_permission($route, false);
            if (! $permissionName || $this->isVendorRoute($route) || str_starts_with($permissionName, 'search.')) {
                $bar->advance();

                continue;
            }

            $guard = explode(':', Arr::first(array_intersect($route->middleware(), $guards)));

            $permission = Permission::findOrCreate($permissionName, $guard[1] ?? $defaultGuard);

            unset($currentPermissions[$permission->id]);

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

        foreach ($currentPermissions as $id => $currentPermission) {
            Permission::query()->whereKey($id)->delete();
        }

        $superAdminRole = Role::findOrCreate('Super Admin');
        User::query()->where('email', '=', 'admin')->first()?->assignRole($superAdminRole);

        $this->newLine();
        $this->info('Permissions initiated!');

        return 0;
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

        return str_starts_with($path, base_path('vendor'));
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
