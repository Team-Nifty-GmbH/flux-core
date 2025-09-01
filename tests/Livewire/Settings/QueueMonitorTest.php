<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\QueueMonitor;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(QueueMonitor::class)
        ->assertStatus(200);
});
