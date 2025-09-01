<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\WorkTimeTypes;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(WorkTimeTypes::class)
        ->assertStatus(200);
});
