<?php

namespace FluxErp\Logging;

use Monolog\Logger;

class DatabaseCustomLogger
{
    /**
     * Create a custom Monolog instance.
     */
    public function __invoke(array $config): Logger
    {
        $logger = new Logger('DatabaseLoggingHandler');

        return $logger->pushHandler(new DatabaseLoggingHandler());
    }
}
