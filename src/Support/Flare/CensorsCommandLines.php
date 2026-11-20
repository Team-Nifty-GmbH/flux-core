<?php

namespace FluxErp\Support\Flare;

trait CensorsCommandLines
{
    protected function censorCommandLine(array $arguments): array
    {
        $isArtisanCommand = basename((string) data_get($arguments, 0)) === 'artisan'
            && ! str_starts_with((string) data_get($arguments, 1), '-');

        return array_slice($arguments, 0, $isArtisanCommand ? 2 : 1);
    }
}
