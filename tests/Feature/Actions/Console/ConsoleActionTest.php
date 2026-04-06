<?php

use FluxErp\Actions\Console\RunCommand;

test('run command requires command', function (): void {
    RunCommand::assertValidationErrors([], 'command');
});

test('run command rejects nonexistent class', function (): void {
    RunCommand::assertValidationErrors([
        'command' => 'NonExistent\\Command\\Class',
    ], 'command');
});
