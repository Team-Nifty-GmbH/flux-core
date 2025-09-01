<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\ActivityLogs;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ActivityLogs::class)
        ->assertStatus(200);
});
