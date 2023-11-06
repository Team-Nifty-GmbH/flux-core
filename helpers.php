<?php

if (! function_exists('route_to_permission')) {
    function route_to_permission(Illuminate\Routing\Route $route = null, bool $checkPermission = true): ?string
    {
        $route = $route ?: \Illuminate\Support\Facades\Route::current();

        if ($route === null) {
            return null;
        }

        $guards = array_keys(\Illuminate\Support\Arr::prependKeysWith(config('auth.guards'), 'auth:'));
        // Add auth as it's the default guard but still guarded
        $guards[] = 'auth';
        $defaultGuard = config('auth.defaults.guard');
        $guard = explode(':', \Illuminate\Support\Arr::first(array_intersect($route->middleware(), $guards)));

        // Allow if route is not guarded in any way.
        if (! array_intersect($route->middleware(), $guards) || ! $route->getPermissionName()) {
            return null;
        }

        try {
            $permission = \Spatie\Permission\Models\Permission::findByName($route->getPermissionName(), $guard[1] ?? $defaultGuard);
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            $permission = null;
        }

        return $checkPermission ? $permission?->name : $route->getPermissionName();
    }
}

if (! function_exists('user_can')) {
    function user_can(Illuminate\Routing\Route|string|array $permission): bool
    {
        $permissionName = $permission instanceof \Illuminate\Routing\Route ?
            route_to_permission($permission) :
            $permission;

        return auth()->user() && auth()->user()->can($permissionName);
    }
}

if (! function_exists('get_subclasses_of')) {
    function get_subclasses_of(string $extendingClass, array|string $namespace): array
    {
        if (! class_exists($extendingClass) && ! interface_exists($extendingClass)) {
            throw new InvalidArgumentException($extendingClass . ' is not an existing class.');
        }

        $autoload = array_keys(include base_path('/vendor/composer/autoload_classmap.php'));

        $namespaces = (array) $namespace;

        foreach ($namespaces as $key => $namespace) {
            $namespace = ltrim($namespace, '\\/');
            $namespace = str_replace('/', '\\', $namespace);

            $namespaces[$key] = $namespace;
        }

        $classes = array_filter($autoload, function ($item) use ($namespaces) {
            return \Illuminate\Support\Str::startsWith($item, $namespaces);
        });

        $subclasses = [];
        foreach ($classes as $class) {
            try {
                if (is_subclass_of($class, $extendingClass)) {
                    $subclasses[] = $class;
                }
            } catch (Throwable $e) {
            }
        }

        return $subclasses;
    }
}

if (! function_exists('channel_to_permission')) {
    function channel_to_permission(string $channelName): ?string
    {
        $exploded = explode('.', $channelName);

        $id = null;
        if (str_starts_with(last($exploded), '{')) {
            array_pop($exploded);
            $id = '.{id}';
        }

        $table = (new (implode('\\', $exploded)))->getTable();

        return 'api.' . str_replace('_', '-', $table) . $id . '.get';
    }
}

if (! function_exists('qualify_model')) {
    function qualify_model(string $model = null): ?string
    {
        if (
            str_contains($model, '\\')
            && class_exists($model)
            && is_a($model, \Illuminate\Database\Eloquent\Model::class, true)
        ) {
            return get_class(new $model());
        }

        $model = ltrim($model, '\\/');

        $model = str_replace('/', '\\', $model);
        $fluxModels = array_values(config('flux.models'));
        $models = \TeamNiftyGmbH\DataTable\Helpers\ModelFinder::all();
        $models = $models->merge($fluxModels);

        return $models->filter(fn ($item) => str_ends_with(strtolower($item), '\\' . strtolower($model)))->first()
            ?: $model;
    }
}

if (! function_exists('gross_to_net')) {
    function gross_to_net(string $gross, ?string $taxRate): string
    {
        if (! $taxRate || $taxRate == 0) {
            return $gross;
        }

        return bcdiv($gross, bcadd(1, $taxRate, 4), 9);
    }
}

if (! function_exists('net_to_gross')) {
    function net_to_gross(string $net, ?string $taxRate): string
    {
        if (! $taxRate || $taxRate == 0) {
            return $net;
        }

        return bcmul($net, bcadd(1, $taxRate, 4), 9);
    }
}

if (! function_exists('discount')) {
    function discount(string $price, ?string $discount): string
    {
        if (! $discount || $discount == 0) {
            return $price;
        }

        return bcsub($price, bcmul($price, $discount, 4), 9);
    }
}

if (! function_exists('diff_percentage')) {
    function diff_percentage(string $old, string $new): string
    {
        return bcdiv(bcsub($old, $new, 9), $old, 4);
    }
}

if (! function_exists('event_subscribers')) {
    function event_subscribers(
        string $event,
        int $modelId = null,
        string $modelType = null
    ): Illuminate\Database\Eloquent\Collection {
        if (
            \FluxErp\Models\EventSubscription::query()
                ->where('event', $event)
                ->whereNull('user_id')
                ->exists()
        ) {
            return \FluxErp\Models\User::all();
        }

        $subscriberIds = \FluxErp\Models\EventSubscription::query()
            ->whereNot('user_id', auth()->id())
            ->where(function ($query) use ($event, $modelId, $modelType) {
                $query->where(function ($query) use ($event) {
                    $query->where('event', $event)
                        ->whereNull('model_id');
                })
                    ->orWhere(function ($query) use ($event, $modelId, $modelType) {
                        $query->where('event', $event)
                            ->when($modelType, function ($query) use ($modelType) {
                                $query->where('model_type', $modelType);
                            })
                            ->when($modelId && $modelType, function ($query) use ($modelId) {
                                $query->where('model_id', $modelId);
                            });
                    })
                    ->orWhere('event', '*');
            })
            ->get()
            ->pluck('user_id')
            ->toArray();

        return \FluxErp\Models\User::query()->whereIntegerInRaw('id', $subscriberIds)->get();
    }
}

if (! function_exists('eloquent_model_event')) {
    function eloquent_model_event(string $event, string $model): string
    {
        $event = strtolower(str_starts_with($event, 'eloquent.') ? substr($event, 9) : $event);

        $modelClass = \TeamNiftyGmbH\DataTable\Helpers\ModelFinder::all()->merge(config('flux.models'))->filter(
            function ($item) use ($model) {
                return str_ends_with(strtolower($item), strtolower($model));
            }
        )->first();

        if (! $modelClass) {
            throw new InvalidArgumentException('Invalid model: ' . $model);
        }

        if (! in_array(
            $event,
            [
                'retrieved',
                'creating',
                'created',
                'updating',
                'updated',
                'saving',
                'saved',
                'deleting',
                'deleted',
                'trashed',
                'forceDeleted',
                'restoring',
                'restored',
            ]
        )
        ) {
            throw new InvalidArgumentException('Invalid event: ' . $event);
        }

        return 'eloquent.' . $event . ': ' . $modelClass;
    }
}

if (! function_exists('to_flat_tree')) {
    function to_flat_tree(array $tree, string $key = 'id', string $parentKey = 'parent_id', array $parent = []): array
    {
        $flat = [];
        $siblings = count($tree);
        $padding = max(strlen($siblings), 2);

        $loop = 1;
        foreach ($tree as $node) {
            $suffix = \Illuminate\Support\Str::padLeft($loop, $padding, '0');

            $node['slug_position'] = ($parent['slug_position'] ?? false)
                ? $parent['slug_position'] . '.' . $suffix
                : $suffix;
            $node['depth'] = substr_count($node['slug_position'], '.');

            $node['has_children'] = (bool) ($node['children'] ?? false);
            $node['has_siblings'] = $siblings > 1;

            //Set primary key if not exists
            $node[$key] = $node[$key] ?? \Illuminate\Support\Str::uuid()->toString();
            $sanitized = \Illuminate\Support\Arr::except($node, 'children');

            //Set parent key
            if ($parent) {
                $sanitized[$parentKey] = $parent[$key];
            }

            $flat[] = $sanitized;

            if ($node['children'] ?? false) {
                $flat = array_merge($flat, to_flat_tree($node['children'], $key, $parentKey, $node));
            }

            $loop++;
        }

        return $flat;
    }
}

if (! function_exists('to_tree')) {
    function to_tree(
        array $flat,
        string $key = 'id',
        string $parentKey = 'parent_id',
        string $childrenKey = 'children'
    ): array {
        $tree = [];
        $lookup = \Illuminate\Support\Arr::keyBy($flat, $key);

        foreach ($lookup as $node) {
            if ($node[$parentKey] ?? false) {
                $lookup[$node[$parentKey]][$childrenKey][] = &$lookup[$node[$key]];
            } else {
                $tree[] = &$lookup[$node[$key]];
            }
        }

        return $tree;
    }
}

if (! function_exists('meilisearch_import_sync')) {
    function meilisearch_import_sync(string $model): int
    {
        if (config('scout.driver') !== 'meilisearch') {
            throw new InvalidArgumentException('Scout driver is not meilisearch.');
        }

        if (! class_exists($model)
            || ! in_array(\Laravel\Scout\Searchable::class, class_uses($model))
            || ! is_subclass_of($model, \Illuminate\Database\Eloquent\Model::class)
        ) {
            throw new InvalidArgumentException('Invalid model: ' . $model);
        }

        \Illuminate\Support\Facades\Artisan::call(\FluxErp\Console\Commands\Scout\ImportCommand::class, [
            'model' => $model,
        ]);

        $client = new \MeiliSearch\Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));
        $from = $client->getTasks((new \MeiliSearch\Contracts\TasksQuery())
            ->setIndexUids([(new $model())->searchableAs()])
            ->setLimit(1))
            ->getFrom();

        $client->waitForTask($from);

        // Sync the settings and wait for the result
        \Illuminate\Support\Facades\Artisan::call(
            \FluxErp\Console\Commands\Scout\SyncIndexSettingsCommand::class,
            ['model' => $model]
        );
        $from = $client->getTasks((new \MeiliSearch\Contracts\TasksQuery())
            ->setIndexUids([(new $model())->searchableAs()])
            ->setLimit(1))
            ->getFrom();
        $client->waitForTask($from);

        return $from;
    }
}

if (! function_exists('faker')) {
    function faker(): Faker\Generator
    {
        return Faker\Factory::create();
    }
}

if (! function_exists('livewire_component_exists')) {
    function livewire_component_exists(string $classOrAlias): bool
    {
        $componentRegistry = app(\Livewire\Mechanisms\ComponentRegistry::class);
        try {
            $class = $componentRegistry->getClass($classOrAlias);
        } catch (\Livewire\Exceptions\ComponentNotFoundException) {
            $class = false;
        }

        try {
            $alias = $componentRegistry->getName($classOrAlias);
        } catch (\Livewire\Exceptions\ComponentNotFoundException) {
            $alias = false;
        }

        return $class || is_string($alias);
    }
}

if (! function_exists('bcround')) {
    function bcround(string $number, int $precision = 0): string
    {
        if (str_contains($number, '.')) {
            return $number[0] !== '-' ?
                bcadd($number, '0.' . str_repeat('0', $precision) . '5', $precision) :
                bcsub($number, '0.' . str_repeat('0', $precision) . '5', $precision);
        }

        return $number;
    }
}

if (! function_exists('flux_path')) {
    function flux_path(string $path = ''): string
    {
        return __DIR__ . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (! function_exists('model')) {
    function model(string $class): Illuminate\Database\Eloquent\Model
    {
        return app($class);
    }
}
