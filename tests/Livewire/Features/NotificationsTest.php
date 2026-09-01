<?php

use FluxErp\Livewire\Features\Notifications;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Notifications::class)
        ->assertOk();
});

test('recomputes the unread count when notifications change', function (): void {
    $component = Livewire::actingAs($this->user)
        ->test(Notifications::class)
        ->assertSet('unread', 0);

    $this->user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'test',
        'data' => [],
    ]);

    $component->dispatch('notifications-changed')
        ->assertSet('unread', 1);
});
