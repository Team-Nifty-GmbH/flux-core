<?php

namespace FluxErp\States\QueueMonitor;

use FluxErp\States\EndableState;
use Spatie\ModelStates\StateConfig;
use TeamNiftyGmbH\DataTable\Contracts\HasFrontendFormatter;

abstract class QueueMonitorState extends EndableState implements HasFrontendFormatter
{
    public static function config(): StateConfig
    {
        return data_get(static::$config, static::class) ?? parent::config();
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
