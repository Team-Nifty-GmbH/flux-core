<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Contact\Leads;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Leads::class)
        ->assertStatus(200);
});
