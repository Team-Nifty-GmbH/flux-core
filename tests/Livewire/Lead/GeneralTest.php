<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Lead\General;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(General::class)
        ->assertStatus(200);
});
