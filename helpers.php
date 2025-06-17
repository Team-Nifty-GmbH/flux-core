<?php

if (! function_exists('all_models')) {
    function all_models(): Illuminate\Support\Collection
    {
        return TeamNiftyGmbH\DataTable\Helpers\ModelFinder::all()
            ->merge(
                TeamNiftyGmbH\DataTable\Helpers\ModelFinder::all(
                    flux_path('src/Models'),
                    flux_path('src'),
                    'FluxErp'
                )
            );
    }
}

if (! function_exists('model_info_all')) {
    function model_info_all(): Illuminate\Support\Collection
    {
        return TeamNiftyGmbH\DataTable\Helpers\ModelInfo::forAllModels()
            ->merge(
                TeamNiftyGmbH\DataTable\Helpers\ModelInfo::forAllModels(
                    flux_path('src/Models'),
                    flux_path('src'),
                    'FluxErp')
            );
    }
}

if (! function_exists('get_models_with_trait')) {
    function get_models_with_trait(string $trait, ?callable $mapFn = null): array
    {
        $morphMap = Illuminate\Database\Eloquent\Relations\Relation::morphMap();

        return collect($morphMap)
            ->filter(function ($modelClass) use ($trait) {
                return in_array($trait, class_uses_recursive($modelClass));
            })
            ->map($mapFn ?? function ($modelClass, $morphAlias) {
                return [
                    'label' => __(class_basename($modelClass)),
                    'id' => $morphAlias,
                ];
            })
            ->values()
            ->toArray();
    }
}

if (! function_exists('route_to_permission')) {
    function route_to_permission(Illuminate\Routing\Route|string|null $route = null, bool $checkPermission = true): ?string
    {
        $route = is_string($route) ? Illuminate\Support\Facades\Route::getRoutes()->getByName($route) : $route;
        $route = $route ?: Illuminate\Support\Facades\Route::current();

        if (is_null($route)) {
            return null;
        }

        $guards = array_keys(config('auth.guards'));
        // Add auth as it's the default guard but still guarded
        $authGuard = Illuminate\Support\Arr::first(Illuminate\Support\Arr::where(
            $route->middleware(),
            fn ($value) => str_starts_with($value, 'auth:')
        ));

        $guard = explode(',', explode(':', $authGuard)[1] ?? '');
        $defaultGuard = config('auth.defaults.guard');

        // Allow if route is not guarded in any way.
        if (! array_intersect($guard, $guards) || ! $route->getPermissionName()) {
            return null;
        }

        if (! $checkPermission) {
            return $route->getPermissionName();
        }

        try {
            $permission = resolve_static(
                Spatie\Permission\Models\Permission::class,
                'findByName',
                [
                    'name' => $route->getPermissionName(),
                    'guardName' => $guard[0] ?? $defaultGuard,
                ]
            );
        } catch (Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            $permission = null;
        }

        return $permission?->name;
    }
}

if (! function_exists('user_can')) {
    function user_can(Illuminate\Routing\Route|string|array $permission): bool
    {
        $permissionName = $permission instanceof Illuminate\Routing\Route ?
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
            return Illuminate\Support\Str::startsWith($item, $namespaces);
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
    function qualify_model(?string $model = null): ?string
    {
        if (
            str_contains($model, '\\')
            && class_exists($model)
            && is_a($model, Illuminate\Database\Eloquent\Model::class, true)
        ) {
            return resolve_static($model, 'class');
        }

        $model = ltrim($model, '\\/');

        $model = str_replace('/', '\\', $model);
        $models = all_models();

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
        if (bccomp($old, 0) === 0 || bccomp($new, 0) === 0) {
            return '0';
        }

        return bcround(bcdiv(bcsub($old, $new, 9), $old, 9), 4);
    }
}

if (! function_exists('percentage_of')) {
    function percentage_of(string|float|int|null $total, string|float|int|null $part): string
    {
        $total ??= '0';
        $part ??= '0';

        if (bccomp($total, 0) === 0 || bccomp($part, 0) === 0) {
            return '0';
        }

        return bcround(bcmul(bcdiv($part, $total, 9), 100, 9), 4);
    }
}

if (! function_exists('event_subscribers')) {
    function event_subscribers(
        string $event,
        ?int $modelId = null,
        ?string $modelType = null
    ): Illuminate\Support\Collection {
        return resolve_static(FluxErp\Models\EventSubscription::class, 'query')
            ->when(
                auth()->user(),
                function (Illuminate\Database\Eloquent\Builder $query): void {
                    $query->whereNot(function (Illuminate\Database\Eloquent\Builder $query): void {
                        $query->where('subscribable_type', auth()->user()->getMorphClass())
                            ->where('subscribable_id', auth()->id());
                    });
                }
            )
            ->where(function (Illuminate\Database\Eloquent\Builder $query) use ($event, $modelId, $modelType): void {
                $query->where(function (Illuminate\Database\Eloquent\Builder $query) use ($event): void {
                    $query->where('event', $event)
                        ->whereNull('model_id');
                })
                    ->orWhere(function ($query) use ($event, $modelId, $modelType): void {
                        $query->where('event', $event)
                            ->when($modelType, function ($query) use ($modelType): void {
                                $query->where('model_type', $modelType);
                            })
                            ->when($modelId && $modelType, function ($query) use ($modelId): void {
                                $query->where('model_id', $modelId);
                            });
                    })
                    ->orWhere('event', '*');
            })
            ->with('subscribable')
            ->get(['subscribable_id', 'subscribable_type'])
            ->pluck('subscribable');
    }
}

if (! function_exists('eloquent_model_event')) {
    function eloquent_model_event(string $event, string $model): string
    {
        $event = strtolower(str_starts_with($event, 'eloquent.') ? substr($event, 9) : $event);

        $modelClass = all_models()
            ->filter(function ($item) use ($model) {
                return str_ends_with(strtolower($item), strtolower($model));
            })
            ->first();

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
    function to_flat_tree(
        array|Illuminate\Contracts\Support\Arrayable $tree,
        string $key = 'id',
        string $parentKey = 'parent_id',
        array $parent = []
    ): array {
        $flat = [];
        $tree = $tree instanceof Illuminate\Contracts\Support\Arrayable ? $tree->toArray() : $tree;
        $siblings = count($tree);
        $padding = max(strlen($siblings), 2);

        $loop = 1;
        foreach ($tree as $node) {
            $suffix = Illuminate\Support\Str::padLeft($loop, $padding, '0');

            $node['slug_position'] = ($parent['slug_position'] ?? false)
                ? $parent['slug_position'] . '.' . $suffix
                : $suffix;
            $node['depth'] = substr_count($node['slug_position'], '.');

            $node['has_children'] = (bool) ($node['children'] ?? false);
            $node['has_siblings'] = $siblings > 1;

            // Set primary key if not exists
            $node[$key] = $node[$key] ?? Illuminate\Support\Str::uuid()->toString();
            $sanitized = Illuminate\Support\Arr::except($node, 'children');

            // Set parent key
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
        $lookup = Illuminate\Support\Arr::keyBy($flat, $key);

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
            || ! in_array(Laravel\Scout\Searchable::class, class_uses($model))
            || ! is_subclass_of($model, Illuminate\Database\Eloquent\Model::class)
        ) {
            throw new InvalidArgumentException('Invalid model: ' . $model);
        }

        Illuminate\Support\Facades\Artisan::call(FluxErp\Console\Commands\Scout\ImportCommand::class, [
            'model' => $model,
        ]);

        $client = new MeiliSearch\Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));
        $from = $client->getTasks((new MeiliSearch\Contracts\TasksQuery())
            ->setIndexUids([app($model)->searchableAs()])
            ->setLimit(1))
            ->getFrom();

        $client->waitForTask($from);

        // Sync the settings and wait for the result
        Illuminate\Support\Facades\Artisan::call(
            FluxErp\Console\Commands\Scout\SyncIndexSettingsCommand::class,
            ['model' => $model]
        );
        $from = $client->getTasks((new MeiliSearch\Contracts\TasksQuery())
            ->setIndexUids([app($model)->searchableAs()])
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
        $componentRegistry = app(Livewire\Mechanisms\ComponentRegistry::class);
        try {
            $class = $componentRegistry->getClass($classOrAlias);
        } catch (Livewire\Exceptions\ComponentNotFoundException) {
            $class = false;
        }

        try {
            $alias = $componentRegistry->getName($classOrAlias);
        } catch (Livewire\Exceptions\ComponentNotFoundException) {
            $alias = false;
        }

        return $class || is_string($alias);
    }
}

if (! function_exists('bcabs')) {
    function bcabs(string $number): string
    {
        return $number[0] === '-' ? substr($number, 1) : $number;
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

if (! function_exists('bcceil')) {
    function bcceil(string $number): string
    {
        if (! str_contains($number, '.')) {
            return $number;
        }

        return $number[0] != '-' ? bcadd($number, 1, 0) : bcsub($number, 0, 0);
    }
}

if (! function_exists('bcfloor')) {
    function bcfloor(string $number): string
    {
        if (! str_contains($number, '.')) {
            return $number;
        }

        return $number[0] != '-' ? bcadd($number, 0, 0) : bcsub($number, 1, 0);
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

if (! function_exists('resolve_static')) {
    function resolve_static(string $class, string $method, ?array $parameters = null): mixed
    {
        $abstract = Illuminate\Support\Facades\App::getAlias($class);
        $binding = Illuminate\Support\Facades\App::getBindings()[$abstract] ?? null;

        if ($binding) {
            $reflector = new Laravel\SerializableClosure\Support\ReflectionClosure($binding['concrete']);
            $concrete = $reflector->getUseVariables()['concrete'] ?? null;
        } else {
            $concrete = $abstract;
        }

        if (! $concrete) {
            throw new InvalidArgumentException('Invalid class: ' . $class);
        }

        if ($method === 'class') {
            return $concrete;
        }

        try {
            $reflectionClass = new ReflectionClass($concrete);
            $reflectionMethod = $reflectionClass->getMethod($method);

            if (! $reflectionMethod->isStatic()) {
                throw new InvalidArgumentException('Method is not static: ' . $method);
            }

            if ($reflectionMethod->getParameters() && is_array($parameters)) {
                return $concrete::$method(...$parameters);
            } else {
                return $concrete::$method();
            }
        } catch (ReflectionException) {
            throw new InvalidArgumentException('Invalid method: ' . $method);
        }
    }
}

if (! function_exists('class_to_broadcast_channel')) {
    function class_to_broadcast_channel(string $class, bool $withParam = true): string
    {
        if (! class_exists($class)) {
            throw new InvalidArgumentException('Invalid class: ' . $class);
        }

        return str_replace('\\', '.', $class)
            . ($withParam ? '.{' . Illuminate\Support\Str::camel(class_basename($class)) . '}' : '');
    }
}

if (! function_exists('morph_alias')) {
    function morph_alias(string $class): string
    {
        $class = resolve_static($class, 'class');

        if (in_array(FluxErp\Traits\HasParentMorphClass::class, class_uses_recursive($class))) {
            return $class::getParentMorphClass();
        }

        /** @var Illuminate\Database\Eloquent\Model $class */
        return Illuminate\Database\Eloquent\Relations\Relation::getMorphAlias($class);
    }
}

if (! function_exists('morphed_model')) {
    function morphed_model(string $alias): ?string
    {
        $class = Illuminate\Database\Eloquent\Relations\Relation::getMorphedModel($alias);

        if (is_null($class)) {
            return null;
        }

        return resolve_static($class, 'class');
    }
}

if (! function_exists('morph_to')) {
    function morph_to(?string $type = null, ?int $id = null, bool $returnBuilder = false): Illuminate\Database\Eloquent\Model|Illuminate\Database\Eloquent\Builder|null
    {
        if (is_null($id) && str_contains($type ?? '', ':')) {
            [$type, $id] = explode(':', $type);
        }

        if (is_null($id) || is_null($type)) {
            return null;
        }

        /** @var Illuminate\Database\Eloquent\Model $model */
        $model = morphed_model($type);
        $query = $model::query()->whereKey($id);

        return $returnBuilder ? $query : $query->first();
    }
}
