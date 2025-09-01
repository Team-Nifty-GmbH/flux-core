<?php

uses(FluxErp\Tests\TestCase::class);
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::withoutLazyLoading()
        ->test($this->livewireComponent)
        ->assertStatus(200);
});
