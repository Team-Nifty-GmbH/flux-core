<?php

namespace FluxErp\Http\Middleware;

use Closure;
use FluxErp\Models\Permission;
use FluxErp\Traits\Model\HasModelPermission;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionMethod;
use ReflectionNamedType;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Guard;
use Spatie\Permission\Traits\HasRoles;
use Symfony\Component\HttpFoundation\Response;

class SearchModelPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $model = $request->route('model');

        if (
            ! $user
            || ! is_string($model)
            || ! in_array(HasRoles::class, class_uses_recursive($user))
            || $user->hasRole('Super Admin')
        ) {
            return $next($request);
        }

        $model = morphed_model($model) ?? qualify_model(str_replace('/', '\\', $model));

        if (! $model || ! class_exists($model) || ! is_a($model, Model::class, true)) {
            return $next($request);
        }

        $instance = app($model);
        $this->authorize($user, $instance);

        foreach ($this->relationPaths($request) as $path) {
            $related = $instance;

            foreach (explode('.', $path) as $segment) {
                $relation = $this->relation($related, $segment);

                if (! $relation) {
                    break;
                }

                abort_if($relation instanceof MorphTo, 403);

                $related = $relation->getRelated();
                $this->authorize($user, $related);
            }
        }

        return $next($request);
    }

    protected function authorize(Authenticatable $user, Model $model): void
    {
        if (
            ! in_array(HasModelPermission::class, class_uses_recursive($model))
            || ! resolve_static($model::class, 'hasPermission')
        ) {
            return;
        }

        $permissions = resolve_static(Permission::class, 'query')
            ->where('name', 'model.' . morph_alias($model::class) . '.get')
            ->whereIn('guard_name', Guard::getNames($user))
            ->get(['id', 'name', 'guard_name']);

        if ($permissions->isNotEmpty() && ! $user->hasAnyPermission($permissions)) {
            throw UnauthorizedException::forPermissions(
                $permissions->pluck('name')
                    ->all()
            );
        }
    }

    protected function relation(Model $model, string $name): ?Relation
    {
        if (! $model->relationResolver($model::class, $name)) {
            if (! method_exists($model, $name)) {
                return null;
            }

            $type = (new ReflectionMethod($model, $name))->getReturnType();

            if (! $type instanceof ReflectionNamedType || ! is_a($type->getName(), Relation::class, true)) {
                return null;
            }
        }

        $relation = $model->{$name}();

        return $relation instanceof Relation ? $relation : null;
    }

    protected function relationPaths(Request $request): array
    {
        $relationArgument = fn (mixed $value): mixed => is_array($value)
            ? data_get($value, 'relation') ?? Arr::first($value)
            : $value;

        $paths = array_merge(
            array_map(
                fn (mixed $field): mixed => is_string($field) && str_contains($field, '.')
                    ? Str::beforeLast($field, '.')
                    : null,
                Arr::wrap($request->input('searchFields'))
            ),
            $this->withPaths(Arr::wrap($request->input('with'))),
            Arr::wrap($request->input('fields')),
            Arr::wrap($request->input('appends')),
            Arr::wrap($request->input('whereHas')),
            Arr::wrap($request->input('whereDoesntHave')),
            [
                $relationArgument($request->input('whereRelation')),
                $relationArgument($request->input('whereDoesntHaveRelation')),
                $relationArgument($request->input('doesntHave')),
            ],
        );

        return array_unique(array_filter($paths, 'is_string'));
    }

    protected function withPaths(array $with, string $prefix = ''): array
    {
        $paths = [];

        foreach ($with as $key => $value) {
            $path = is_string($key) ? $key : $value;

            if (is_string($path)) {
                $path = $prefix . Str::before($path, ':');
                $paths[] = $path;
            }

            if (is_array($value)) {
                $paths = array_merge(
                    $paths,
                    $this->withPaths($value, is_string($key) ? $path . '.' : $prefix)
                );
            }
        }

        return $paths;
    }
}
