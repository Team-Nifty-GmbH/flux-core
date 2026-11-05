<?php

namespace Spatie\FlareClient\Senders;

use Closure;

interface Sender
{
    public function post(string $endpoint, string $apiToken, array $payload, mixed $type, bool $test, Closure $callback): void;
}
