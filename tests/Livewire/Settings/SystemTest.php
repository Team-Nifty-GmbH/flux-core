<?php

uses(FluxErp\Tests\TestCase::class);
use Livewire\Livewire;
use FluxErp\Livewire\Settings\System;

test('renders successfully', function (): void {
    Livewire::withoutLazyLoading()
        ->test(System::class)
        ->assertStatus(200);
});
