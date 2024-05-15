<?php

namespace FluxErp\Helpers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class Helper
{
    public static function checkCycle(string $model, object $item, int $parentId): bool
    {
        $model = app($model);
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

        $canCreate = resolve_static($createAction, 'canPerformAction', [false]);
        $canUpdate = resolve_static($updateAction, 'canPerformAction', [false]);
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

        if (resolve_static($deleteAction, 'canPerformAction', [false])) {
            foreach (array_diff($existing, $updated) as $deleted) {
                try {
                    $deleteAction::make(['id' => $deleted])->validate()->execute();
                } catch (ValidationException) {
                }
            }
        }
    }

    /**
     *  [
     *      'start' => string | valid datetime string,
     *      'interval' => int | min 1,
     *      'unit' => string | ['days', 'weeks', 'months', 'years'],
     *      'weekdays' => array | ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
     *      'monthly' => string | ['day', 'first', 'second', 'third', 'fourth', 'last'],
     *  ]
     */
    public static function buildRepeatStringFromArray(array $repeatArray): ?string
    {
        if (in_array($repeatArray['unit'], ['days', 'years'])
            || ($repeatArray['unit'] === 'months' && ($repeatArray['monthly'] ?? false) === 'day')
        ) {
            return '+' . $repeatArray['interval'] . ' ' . $repeatArray['unit'];
        } elseif ($repeatArray['unit'] === 'weeks') {
            return implode(',', array_map(
                fn ($item) => 'next ' . $item . ' +' . $repeatArray['interval'] - 1 . ' ' . $repeatArray['unit'],
                array_intersect(
                    array_map(
                        fn ($item) => Carbon::parse($repeatArray['start'])->addDays($item)->format('D'),
                        range(0, 6)
                    ),
                    $repeatArray['weekdays'],
                )
            ));
        } elseif ($repeatArray['unit'] === 'months') {
            return $repeatArray['monthly'] . ' '
                . Carbon::parse($repeatArray['start'])->format('D') . ' of +'
                . $repeatArray['interval'] . ' ' . $repeatArray['unit'];
        }

        return null;
    }

    public static function parseRepeatStringToArray(string $repeatString): array
    {
        $repeatable = explode(',', $repeatString);
        $interval = null;
        $repeat = match (true) {
            str_contains($repeatable[0], 'year') => [
                'unit' => 'years',
            ],
            str_contains($repeatable[0], 'month') => [
                'unit' => 'months',
                'monthly' => str_contains($repeatable[0], 'of') ? explode(' ', $repeatable[0])[0] : 'day',
            ],
            str_contains($repeatable[0], 'week') => [
                'unit' => 'weeks',
            ],
            str_contains($repeatable[0], 'day') => [
                'unit' => 'days',
            ],
            default => [
                'unit' => null,
            ]
        };

        preg_match('~\+(.*?) ~', $repeatable[0], $interval);

        if ($repeat['unit'] === 'weeks') {
            $repeat['interval'] = ! is_bool($interval[1] ?? false) ? $interval[1] + 1 : null;
            $repeat['weekdays'] = array_map(
                fn ($item) => trim(explode(' ', explode('+', $item)[0])[1]),
                $repeatable
            );
        } else {
            $repeat['interval'] = $interval[1] ?? null;
        }

        return $repeat;
    }
}
