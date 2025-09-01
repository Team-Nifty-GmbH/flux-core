<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\Tokens;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Tokens::class)
        ->assertStatus(200);
});
