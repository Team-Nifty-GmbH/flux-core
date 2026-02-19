<?php

use FluxErp\Livewire\Settings\Targets;
use FluxErp\Models\Target;
use FluxErp\Models\User;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Targets::class)
        ->assertOk();
});

test('edit preserves saved dropdown values', function (): void {
    $target = Target::query()->create([
        'name' => 'Test Target',
        'model_type' => 'order',
        'timeframe_column' => 'invoice_date',
        'aggregate_type' => 'sum',
        'aggregate_column' => 'total_net_price',
        'owner_column' => 'agent_id',
        'target_value' => 1000,
        'start_date' => now()->startOfMonth(),
        'end_date' => now()->endOfMonth(),
    ]);

    Livewire::test(Targets::class)
        ->call('edit', $target->getKey())
        ->assertSet('target.timeframe_column', 'invoice_date')
        ->assertSet('target.aggregate_type', 'sum')
        ->assertSet('target.aggregate_column', 'total_net_price')
        ->assertSet('target.owner_column', 'agent_id');
});

test('edit populates selected user ids', function (): void {
    $user = User::factory()->create([
        'is_active' => true,
        'language_id' => $this->defaultLanguage->getKey(),
    ]);

    $target = Target::query()->create([
        'name' => 'Test Target',
        'model_type' => 'order',
        'timeframe_column' => 'order_date',
        'aggregate_type' => 'count',
        'aggregate_column' => 'id',
        'owner_column' => 'approval_user_id',
        'target_value' => 50,
        'start_date' => now()->startOfMonth(),
        'end_date' => now()->endOfMonth(),
    ]);

    $target->users()->attach($user->getKey(), [
        'target_share' => 100,
        'is_percentage' => true,
    ]);

    Livewire::test(Targets::class)
        ->call('edit', $target->getKey())
        ->assertSet('selectedUserIds', [$user->getKey()]);
});

test('edit for new target resets selected user ids', function (): void {
    $user = User::factory()->create([
        'is_active' => true,
        'language_id' => $this->defaultLanguage->getKey(),
    ]);

    $target = Target::query()->create([
        'name' => 'Test Target',
        'model_type' => 'order',
        'timeframe_column' => 'order_date',
        'aggregate_type' => 'count',
        'aggregate_column' => 'id',
        'owner_column' => 'approval_user_id',
        'target_value' => 50,
        'start_date' => now()->startOfMonth(),
        'end_date' => now()->endOfMonth(),
    ]);

    $target->users()->attach($user->getKey(), [
        'target_share' => 100,
        'is_percentage' => true,
    ]);

    Livewire::test(Targets::class)
        ->call('edit', $target->getKey())
        ->assertSet('selectedUserIds', [$user->getKey()])
        ->call('edit')
        ->assertSet('selectedUserIds', []);
});
