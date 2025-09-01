<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Contact\Accounting\General;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(General::class)
        ->assertStatus(200);
});
