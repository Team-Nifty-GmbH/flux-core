<?php

namespace FluxErp\States\Task;

use FluxErp\States\EndableState;
use Spatie\ModelStates\StateConfig;
use TeamNiftyGmbH\DataTable\Contracts\HasFrontendFormatter;

abstract class TaskState extends EndableState implements HasFrontendFormatter
{
    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return data_get(static::$config, static::class) ?? parent::config()
            ->default(Open::class)
            ->allowTransitions([
                [
                    [
                        Open::class,
                        Done::class,
                        Canceled::class,
                    ],
                    InProgress::class,
                ],
                [
                    [
                        Open::class,
                        InProgress::class,
                    ],
                    Done::class,
                ],
                [
                    [
                        Open::class,
                        InProgress::class,
                    ],
                    Canceled::class,
                ],
                [
                    [
                        InProgress::class,
                        Canceled::class,
                    ],
                    Open::class,
                ],
            ]);
    }

    public static function getFrontendFormatter(...$args): string|array
    {
        return [
            'state',
            self::getStateMapping()
                ->map(fn ($key) => (new $key(''))->color()),
        ];
    }
}
