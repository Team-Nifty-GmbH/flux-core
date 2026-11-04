<?php

namespace FluxErp\Support\Flare;

trait CensorsCommandLines
{
    protected function censorCommandLine(array $arguments): array
    {
        $arguments = array_values(
            array_filter($arguments, fn (mixed $argument): bool => ! str_starts_with((string) $argument, '-'))
        );

        return array_slice($arguments, 0, basename((string) data_get($arguments, 0)) === 'artisan' ? 2 : 1);
    }
}
