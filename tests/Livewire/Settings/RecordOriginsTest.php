<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\RecordOrigins;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RecordOrigins::class)
        ->assertStatus(200);
});
