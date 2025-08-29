<?php

namespace FluxErp\States\Ticket;

use FluxErp\States\EndableState;
use Spatie\ModelStates\StateConfig;
use TeamNiftyGmbH\DataTable\Contracts\HasFrontendFormatter;

abstract class TicketState extends EndableState implements HasFrontendFormatter
{
    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return data_get(static::$config, static::class) ?? parent::config()
            ->default(WaitingForSupport::class)
            ->allowTransitions([
                [
                    [
                        WaitingForSupport::class,
                        WaitingForCustomer::class,
                        InProgress::class,
                    ],
                    Escalated::class,
                ],
                [
                    [
                        WaitingForSupport::class,
                        WaitingForCustomer::class,
                        InProgress::class,
                        Escalated::class,
                    ],
                    Done::class,
                ],
                [
                    [
                        WaitingForSupport::class,
                        WaitingForCustomer::class,
                        Escalated::class,
                    ],
                    InProgress::class,
                ],
                [
                    [
                        WaitingForSupport::class,
                        InProgress::class,
                        Done::class,
                        Escalated::class,
                    ],
                    WaitingForCustomer::class,
                ],
                [
                    [
                        WaitingForCustomer::class,
                        InProgress::class,
                        Done::class,
                        Escalated::class,
                    ],
                    WaitingForSupport::class,
                ],
                [
                    [
                        Done::class,
                    ],
                    Closed::class,
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
