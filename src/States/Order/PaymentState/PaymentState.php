<?php

namespace FluxErp\States\Order\PaymentState;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;
use TeamNiftyGmbH\DataTable\Contracts\HasFrontendFormatter;

abstract class PaymentState extends State implements HasFrontendFormatter
{
    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Open::class)
            ->allowTransitions([
                [
                    [
                        Open::class,
                        PartialPaid::class,
                    ],
                    Paid::class,
                ],
                [
                    [
                        Paid::class,
                        PartialPaid::class,
                    ],
                    Open::class,
                ],
                [
                    [
                        Paid::class,
                        Open::class,
                    ],
                    PartialPaid::class,
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
