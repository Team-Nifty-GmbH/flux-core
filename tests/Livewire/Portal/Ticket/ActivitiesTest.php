<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Portal\Ticket\Activities;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Activities::class)
        ->assertStatus(200);
});
