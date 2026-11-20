<?php

namespace FluxErp\Tests\Fixtures;

use Closure;
use Spatie\FlareClient\Enums\FlareEntityType;
use Spatie\FlareClient\Senders\Sender;

class RecordingFlareSender implements Sender
{
    public static array $payloads = [];

    public function __construct(array $config = []) {}

    public function post(
        string $endpoint,
        string $apiToken,
        array $payload,
        FlareEntityType $type,
        bool $test,
        Closure $callback,
    ): void {
        static::$payloads[] = $payload;
    }
}
