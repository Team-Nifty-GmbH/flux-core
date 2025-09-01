<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\System;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::withoutLazyLoading()
        ->test(System::class)
        ->assertStatus(200);
});
