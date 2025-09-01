<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\FailedJobs;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(FailedJobs::class)
        ->assertStatus(200);
});
