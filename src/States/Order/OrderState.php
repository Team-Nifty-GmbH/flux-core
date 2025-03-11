<?php

namespace FluxErp\States\Order;

use FluxErp\States\State;
use Spatie\ModelStates\StateConfig;
use TeamNiftyGmbH\DataTable\Contracts\HasFrontendFormatter;

abstract class OrderState extends State implements HasFrontendFormatter
{
    public static function config(): StateConfig
    {
        return data_get(static::$config, static::class) ?? parent::config()
            ->default(Draft::class)
            ->allowTransitions([
                [
                    [
                        Draft::class,
                        Canceled::class,
                    ],
                    Open::class,
                ],
                [
                    Open::class,
                    Draft::class,
                ],
                [
                    [
                        Open::class,
                        InProgress::class,
                        ReadyForPacking::class,
                    ],
                    InReview::class,
                ],
                [
                    [
                        Open::class,
                        Done::class,
                        ReadyForPacking::class,
                    ],
                    InProgress::class,
                ],
                [
                    [
                        Open::class,
                        InProgress::class,
                        InReview::class,
                    ],
                    Canceled::class,
                ],
                [
                    [
                        Open::class,
                        InProgress::class,
                        InReview::class,
                    ],
                    Done::class,
                ],
                [
                    [
                        InProgress::class,
                        InReview::class,
                    ],
                    ReadyForPacking::class,
                ],
                [
                    [
                        InProgress::class,
                        InReview::class,
                        ReadyForPacking::class,
                    ],
                    ReadyForDelivery::class,
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

    abstract public function color(): string;
}
