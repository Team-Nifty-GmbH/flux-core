<?php

namespace FluxErp\States\Address;

use FluxErp\States\State;
use Spatie\ModelStates\StateConfig;
use TeamNiftyGmbH\DataTable\Contracts\HasFrontendFormatter;

abstract class AdvertisingState extends State implements HasFrontendFormatter
{
    public static function config(): StateConfig
    {
        return data_get(static::$config, static::class) ?? parent::config()
            ->default(Open::class)
            ->allowTransitions([
                [
                    [
                        Active::class,
                        PendingOptIn::class,
                    ],
                    Open::class,
                ],
                [
                    [
                        Open::class,
                        PendingOptIn::class,
                    ],
                    Active::class,
                ],
                [
                    [
                        Open::class,
                        Active::class,
                    ],
                    PendingOptIn::class,
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
