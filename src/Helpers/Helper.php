<?php

namespace FluxErp\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Arr;

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
        string $namespace = null,
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

    /**
     * @return string[]
     */
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
}
