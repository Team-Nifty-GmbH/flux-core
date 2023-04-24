<?php

return [
    'channels' => [
        'database' => [
            'driver' => 'custom',
            'handler' => \FluxErp\Logging\DatabaseLoggingHandler::class,
            'via' => \FluxErp\Logging\DatabaseCustomLogger::class,
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAYS', 30),
        ],
    ],
];
