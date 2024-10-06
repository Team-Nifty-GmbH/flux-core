<?php

namespace FluxErp\Helpers;

use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use FluxErp\Actions\FluxAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class Helper
{
    public static function checkCycle(string $model, object|int|string $item, int $parentId): bool
    {
        if (is_object($item)) {
            $children[] = data_get($item, 'id');
        } else {
            $children[] = $item;
        }

        for ($i = 0; $i < count($children); $i++) {
            $child = resolve_static($model, 'query')
                ->whereKey($children[$i])
                ->first();
            $children = array_merge($children, $child?->children()->pluck('id')->toArray() ?? []);
        }

        return in_array($parentId, $children);
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

            return class_exists($class) ? get_class(app($class)) : false;
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
        /** @var FluxAction $createAction */
        /** @var FluxAction $updateAction */
        /** @var FluxAction $deleteAction */
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

    public static function getRepetitions(array|Model $repeatable, string $periodStart, string $periodEnd): array
    {
        $repeatable = Arr::wrap($repeatable);

        if (! array_is_list($repeatable)) {
            $repeatable = [$repeatable];
        }

        $repetitions = [];
        foreach ($repeatable as $item) {
            $repeatString = data_get($item, 'repeat');

            if (! $repeatString) {
                $repetitions[] = $item;

                continue;
            }

            $i = 0;
            $events = [];
            $recurrences = data_get($item, 'recurrences');

            for ($j = count($repeatValues = explode(',', $repeatString)); $j > 0; $j--) {
                if (data_get($item, 'recurrences')) {
                    if ($recurrences < 1) {
                        continue;
                    }

                    $datePeriod = new DatePeriod(
                        Carbon::parse(data_get($item, 'start')),
                        DateInterval::createFromDateString($repeatValues[$i]),
                        ($count = (int) ceil($recurrences / $j)) - (int) ($i === 0), // subtract 1, because start date does not count towards recurrences limit
                        (int) ($i !== 0) // 1 = Exclude start date
                    );

                    $recurrences -= $count;
                } else {
                    $datePeriod = new DatePeriod(
                        Carbon::parse(data_get($item, 'start')),
                        DateInterval::createFromDateString($repeatValues[$i]),
                        Carbon::parse(is_null(data_get($item, 'repeat_end')) ?
                            $periodEnd :
                            min([data_get($item, 'repeat_end'), $periodEnd])
                        ),
                        (int) ($i !== 0)
                    );
                }

                // filter dates in between start and end
                $dates = array_filter(
                    iterator_to_array($datePeriod),
                    fn ($dateItem) => ($date = $dateItem->format('Y-m-d H:i:s')) > $periodStart
                        && $date < $periodEnd
                        && ! in_array($date, data_get($item, 'excluded') ?: [])
                        && (
                            ! data_get($item, 'repeat_end')
                            || $date < Carbon::parse(data_get($item, 'repeat_end'))->toDateTimeString()
                        )
                );

                $events = array_merge($events, Arr::mapWithKeys($dates, function ($date, $key) use ($item) {
                    $interval = date_diff(
                        Carbon::parse(data_get($item, 'start')),
                        Carbon::parse(data_get($item, 'end'))
                    );

                    if ($item instanceof Model) {
                        return [
                            $key => (new $item())->forceFill(
                                array_merge(
                                    $item->toArray(),
                                    [
                                        'start' => ($start = Carbon::parse(data_get($item, 'start'))
                                            ->setDateFrom($date))
                                            ->format('Y-m-d H:i:s'),
                                        'end' => $start->add($interval)->format('Y-m-d H:i:s'),
                                    ],
                                    ['id' => data_get($item, 'id') . '|' . $key]
                                )
                            ),
                        ];
                    } else {
                        return [
                            $key => array_merge(
                                $item,
                                [
                                    'start' => ($start = Carbon::parse(data_get($item, 'start'))->setDateFrom($date))
                                        ->format('Y-m-d H:i:s'),
                                    'end' => $start->add($interval)->format('Y-m-d H:i:s'),
                                ],
                                ['id' => data_get($item, 'id') . '|' . $key]
                            ),
                        ];
                    }
                }));

                $i++;
            }

            foreach ($events as $event) {
                $repetitions[] = $event;
            }
        }

        return $repetitions;
    }
}
