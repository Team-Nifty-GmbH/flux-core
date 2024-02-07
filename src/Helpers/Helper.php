<?php

namespace FluxErp\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class Helper
{
    public static function checkCycle(string $model, object $item, int $parentId): bool
    {
        $model = new $model();
        $children[] = $item;
        for ($i = 0; $i < count($children); $i++) {
            $child = $model->query()
                ->whereKey($children[$i]['id'])
                ->first();
            $children = array_merge($children, $child->children()->get()->toArray());
        }

        return in_array($parentId, Arr::pluck($children, 'id'));
    }

    public static function classExists(string $classString,
        ?string $namespace = null,
        bool $isModel = false,
        bool $isEvent = false): string|bool
    {
        if ($isModel || $isEvent) {
            if ($isModel && class_exists($classString) && is_subclass_of($classString, Model::class)) {
                return $classString;
            }

            if ($isEvent && class_exists($classString) && in_array(Dispatchable::class, class_uses($classString))) {
                return $classString;
            }

            $namespaces[] = $isModel ? 'FluxErp\\Models' : 'FluxErp\\Events';
            $modules = file_exists(base_path('modules_statuses.json')) ?
                (array) json_decode(file_get_contents(base_path('modules_statuses.json'))) :
                [];

            foreach ($modules as $key => $module) {
                if ($module) {
                    $namespaces[] = 'Modules\\' . $key . ($isModel ? '\\Models' : '\\Events');
                }
            }

            foreach ($namespaces as $namespace) {
                $class = $namespace . '\\' . ucfirst($classString);

                if (! class_exists($class)) {
                    continue;
                }

                if ($isModel) {
                    if (is_subclass_of($class, Model::class)) {
                        return $class;
                    }
                } elseif ($isEvent) {
                    return $class;
                }
            }

            return false;
        } else {
            $class = $namespace . '\\' . ucfirst($classString);

            return class_exists($class) ? get_class(new $class) : false;
        }
    }

    public static function getHtmlInputFieldTypes(): array
    {
        return [
            'text',
            'number',
            'email',
            'password',
            'range',
            'date',
            'time',
            'tel',
            'color',
            'datetime-local',
            'month',
            'week',
            'url',
            'checkbox',
            'select',
        ];
    }

    public static function updateRelatedRecords(
        Model $model,
        array $related,
        string $relation,
        string $foreignKey,
        string $createAction,
        string $updateAction,
        string $deleteAction
    ): void {
        $relatedKeyName = $model->$relation()->getRelated()->getKeyName();

        $existing = $model->$relation()->pluck($relatedKeyName)->toArray();
        $model->$relation()->whereNotIn($relatedKeyName, Arr::pluck($related, $relatedKeyName))->delete();

        $canCreate = $createAction::canPerformAction(false);
        $canUpdate = $updateAction::canPerformAction(false);
        $updated = [];
        foreach ($related as $item) {
            $item = array_merge($item, [$foreignKey => $model->getKey()]);
            if (! data_get($item, $relatedKeyName)) {
                if ($canCreate) {
                    try {
                        $createAction::make($item)->validate()->execute();
                    } catch (ValidationException) {
                    }
                }
            } else {
                if ($canUpdate) {
                    try {
                        $updateAction::make($item)->validate()->execute();
                    } catch (ValidationException) {
                    }
                    $updated[] = $item['id'];
                }
            }
        }

        if ($deleteAction::canPerformAction(false)) {
            foreach (array_diff($existing, $updated) as $deleted) {
                try {
                    $deleteAction::make(['id' => $deleted])->validate()->execute();
                } catch (ValidationException) {
                }
            }
        }
    }
}
