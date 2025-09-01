<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\Purchase;
use FluxErp\Models\Currency;
use Livewire\Livewire;

beforeEach(function (): void {
    Currency::factory()->create(['is_default' => true]);
});

test('renders successfully', function (): void {
    Livewire::test(Purchase::class)
        ->assertStatus(200);
});
