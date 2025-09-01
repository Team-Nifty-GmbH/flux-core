<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Contact\Accounting\SepaMandates;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SepaMandates::class)
        ->assertStatus(200);
});
