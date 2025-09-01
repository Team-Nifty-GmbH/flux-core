<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\SerialNumberRanges;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SerialNumberRanges::class)
        ->assertStatus(200);
});
