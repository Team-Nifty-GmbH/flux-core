<?php

use FluxErp\Livewire\Widgets\HumanResources\ActiveWorkTimesWidget;
use FluxErp\Models\WorkTime;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ActiveWorkTimesWidget::class)
        ->assertOk();
});

test('shows active unlocked work times', function (): void {
    app(WorkTime::class)->create([
        'user_id' => $this->user->getKey(),
        'started_at' => now()->subHours(2),
        'ended_at' => null,
        'is_daily_work_time' => true,
        'is_pause' => false,
        'is_locked' => false,
        'total_time_ms' => 0,
        'paused_time_ms' => 0,
    ]);

    $component = Livewire::test(ActiveWorkTimesWidget::class)
        ->assertOk();

    $items = $component->get('items');

    expect($items)->toHaveCount(1)
        ->and($items[0]['label'])->toContain($this->user->name);
});

test('excludes locked work times', function (): void {
    app(WorkTime::class)->create([
        'user_id' => $this->user->getKey(),
        'started_at' => now()->subHours(8),
        'ended_at' => now(),
        'is_daily_work_time' => true,
        'is_pause' => false,
        'is_locked' => true,
        'total_time_ms' => 28800000,
        'paused_time_ms' => 0,
    ]);

    $component = Livewire::test(ActiveWorkTimesWidget::class)
        ->assertOk();

    expect($component->get('items'))->toBeEmpty();
});
