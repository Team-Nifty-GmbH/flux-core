<?php

use FluxErp\Tests\BrowserTestCase;

test('browser tests keep the configured database', function (): void {
    config(['database.connections.mysql.database' => 'configured_schema']);

    (new class('environment') extends BrowserTestCase {})->defineEnvironment($this->app);

    expect(config('database.connections.mysql.database'))->toBe('configured_schema');
});
