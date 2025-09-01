<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\LeadStates;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LeadStates::class)
        ->assertStatus(200);
});
