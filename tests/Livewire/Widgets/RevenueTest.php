<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\Revenue;
use FluxErp\Models\Currency;
use Livewire\Livewire;

beforeEach(function (): void {
    Currency::factory()->create([
        'is_default' => true,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(Revenue::class)
        ->assertStatus(200);
});
