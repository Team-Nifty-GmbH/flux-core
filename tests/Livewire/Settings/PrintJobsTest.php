<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\PrintJobs;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PrintJobs::class)
        ->assertStatus(200);
});
