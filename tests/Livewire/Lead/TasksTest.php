<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Lead\Tasks;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Tasks::class)
        ->assertStatus(200);
});
