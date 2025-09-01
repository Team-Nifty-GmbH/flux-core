<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Lead\Lead;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Lead::class)
        ->assertStatus(200);
});
