<?php

use FluxErp\Livewire\Settings\Rules;
use FluxErp\Models\Rule;
use Livewire\Livewire;

test('rules settings component renders', function (): void {
    Livewire::test(Rules::class)
        ->assertOk();
});

test('can edit a rule', function (): void {
    $rule = Rule::factory()->create();

    Livewire::test(Rules::class)
        ->call('edit', $rule)
        ->assertOk();
});
