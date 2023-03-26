<?php

namespace FluxErp\States\Order\DeliveryState;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;
use TeamNiftyGmbH\DataTable\Contracts\HasFrontendFormatter;

abstract class DeliveryState extends State implements HasFrontendFormatter
{
    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Open::class)
            ->allowTransitions([
                [
                    [
                        ReadyForDelivery::class,
                        InProgress::class,
                    ],
                    Open::class,
                ],
                [
                    [
                        Open::class,
                        InProgress::class,
                    ],
                    ReadyForDelivery::class,
                ],
                [
                    [
                        ReadyForDelivery::class,
                    ],
                    InProgress::class,
                ],
                [
                    [
                        InProgress::class,
                    ],
                    PartialDelivered::class,
                ],
                [
                    [
                        PartialDelivered::class,
                        InProgress::class,
                    ],
                    Delivered::class,
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
