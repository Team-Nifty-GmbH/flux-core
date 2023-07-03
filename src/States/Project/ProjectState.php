<?php

namespace FluxErp\States\Project;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;
use TeamNiftyGmbH\DataTable\Contracts\HasFrontendFormatter;

abstract class ProjectState extends State implements HasFrontendFormatter
{
    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Open::class)
            ->allowTransitions([
                [
                    InProgress::class,
                    Open::class,
                ],
                [
                    Open::class,
                    InProgress::class,
                ],
                [
                    InProgress::class,
                    Done::class,
                ],
                [
                    Done::class,
                    InProgress::class,
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
