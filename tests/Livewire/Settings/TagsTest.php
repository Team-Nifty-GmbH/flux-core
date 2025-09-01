<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\Tags;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Tags::class)
        ->assertStatus(200);
});
