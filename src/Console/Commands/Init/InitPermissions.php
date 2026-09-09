<?php

namespace FluxErp\Console\Commands\Init;

use Closure;
use FluxErp\Actions\FluxAction;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Facades\Action;
use FluxErp\Facades\Widget;
use FluxErp\Http\Controllers\Controller;
use FluxErp\Models\Permission;
use FluxErp\Traits\Livewire\WithTabs;
use FluxErp\Traits\Model\HasModelPermission;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
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

    private array $knownPermissions = [];

    private array $preexistingPermissions = [];

    public function handle(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->knownPermissions = resolve_static(Permission::class, 'query')
            ->get(['id', 'name', 'guard_name'])
            ->mapWithKeys(fn (Permission $permission): array => [
                $permission->guard_name . '|' . $permission->name => $permission->getKey(),
            ])
            ->all();

        $this->currentPermissions = array_flip(array_values($this->knownPermissions));
        $this->preexistingPermissions = $this->currentPermissions;

        $this->registerActionPermission();
        $this->registerActionPermission('sanctum');
        $this->registerModelGetPermission();
        $this->registerRoutePermissions();
        $this->registerWidgetPermissions();
        $this->registerTabPermissions();
        $this->registerPrintPermissions();

        resolve_static(Permission::class, 'query')
            ->whereKey(array_keys($this->currentPermissions))
            ->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    protected function keepPermission(string $name, string $guardName = 'web'): int
    {
        $key = $guardName . '|' . $name;

        $id = $this->knownPermissions[$key] ??= resolve_static(Permission::class, 'query')
            ->create(['name' => $name, 'guard_name' => $guardName])
            ->getKey();

        unset($this->currentPermissions[$id]);

        return $id;
    }

    protected function registerActionPermission(string $guardName = 'web'): void
    {
        $this->info('Registering action permissions for guard ' . $guardName . '…');
        foreach (Action::all() as $action) {
            if ($action['class']::hasPermission()) {
                $this->keepPermission('action.' . $action['name'], $guardName);
            }
        }
    }

    protected function registerModelGetPermission(): void
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
                $id = $this->keepPermission('model.' . $alias . '.get');

                if (! array_key_exists($id, $this->preexistingPermissions)) {
                    $newPermissions[] = $id;
                }
            }
        }

        if ($newPermissions) {
            foreach ($roles as $role) {
                /** @var Role $role */
                $role->givePermissionTo($newPermissions);
            }
        }
    }

    protected function registerRoutePermissions(): void
    {
        $routes = Route::getRoutes()->getRoutes();

        $this->info('Registering route permissions…');
        $bar = $this->output->createProgressBar(count($routes));
        foreach ($routes as $route) {
            $permissionName = route_to_permission($route, false);
            if (! $permissionName || $this->isVendorRoute($route) || str_starts_with($permissionName, 'search.')) {
                $bar->advance();

                continue;
            }

            $controllerClass = is_string($route->action['uses'] ?? null) ? $route->getControllerClass() : null;
            if ($controllerClass && is_a($controllerClass, Controller::class, true) && ! $controllerClass::hasPermission()) {
                $bar->advance();

                continue;
            }

            $guards = array_values(array_filter($route->middleware(), fn ($guard) => str_starts_with($guard, 'auth:')));
            $guard = array_shift($guards);

            // omit api routes
            if (! $guard || is_a($route->getAction('controller'), FluxAction::class, true)) {
                continue;
            }

            $authGuards = explode(',', str_replace('auth:', '', $guard));

            foreach ($authGuards as $guard) {
                $this->keepPermission($permissionName, $guard);
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

    protected function registerWidgetPermissions(): void
    {
        $this->info('Registering widget permissions…');
        foreach (Widget::all() as $widget) {
            $this->keepPermission('widget.' . $widget['component_name']);
        }
    }

    protected function registerTabPermissions(): void
    {
        $this->info('Registering tab permissions…');
        $finder = app('livewire.finder');
        foreach (invade($finder)->classComponents as $component) {
            if (! in_array(WithTabs::class, class_uses_recursive($component))) {
                continue;
            }

            $componentInstance = new $component();

            foreach ($componentInstance->renderingWithTabs()->getTabsToRender() as $tab) {
                $this->keepPermission('tab.' . $tab->component);
            }
        }
    }

    protected function registerPrintPermissions(): void
    {
        $this->info('Registering print permissions…');
        $roles = resolve_static(Role::class, 'query')
            ->where('guard_name', 'web')
            ->get();
        $newPermissions = [];

        foreach (Relation::morphMap() as $alias => $class) {
            $model = app($class);
            if ($model instanceof OffersPrinting) {
                foreach ($model->getPrintViews() as $key => $view) {
                    $id = $this->keepPermission(print_view_to_permission($key, $alias));

                    if (! array_key_exists($id, $this->preexistingPermissions)) {
                        $newPermissions[] = $id;
                    }
                }
            }
        }

        if ($newPermissions) {
            foreach ($roles as $role) {
                /** @var Role $role */
                $role->givePermissionTo($newPermissions);
            }
        }
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
