<?php

use FluxErp\Livewire\Settings\VacationCarryoverRules;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(VacationCarryoverRules::class)
        ->assertOk();
});

test('can create vacation carryover rule with expiry date', function (): void {
    Livewire::test(VacationCarryoverRules::class)
        ->call('edit')
        ->set('vacationCarryoverRuleForm.name', 'Carry until March')
        ->set('vacationCarryoverRuleForm.max_days', 5)
        ->set('vacationCarryoverRuleForm.expires_at_month', 3)
        ->set('vacationCarryoverRuleForm.expires_at_day', 31)
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('vacation_carryover_rules', [
        'name' => 'Carry until March',
        'expires_at_month' => 3,
        'expires_at_day' => 31,
    ]);
});

test('expiry month is not limited to the following year', function (): void {
    Livewire::test(VacationCarryoverRules::class)
        ->call('edit')
        ->set('vacationCarryoverRuleForm.name', 'Carry for 18 months')
        ->set('vacationCarryoverRuleForm.expires_at_month', 18)
        ->set('vacationCarryoverRuleForm.expires_at_day', 1)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('vacation_carryover_rules', [
        'name' => 'Carry for 18 months',
        'expires_at_month' => 18,
    ]);
});

test('form fields bind to existing form properties', function (): void {
    Livewire::test(VacationCarryoverRules::class)
        ->assertDontSeeHtml('vacationCarryoverRuleForm.expires_after_months')
        ->assertSeeHtml('vacationCarryoverRuleForm.expires_at_month')
        ->assertSeeHtml('vacationCarryoverRuleForm.expires_at_day');
});
