<?php

use FluxErp\Livewire\Features\Notifications;
use Illuminate\Http\Request;
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

test('shows the toast when the user is already on the accept url', function (): void {
    $url = url('/orders/280');

    app()->rebinding(
        'request',
        fn ($app, Request $request) => $request->headers->set('referer', $url)
    );

    Livewire::actingAs($this->user)
        ->test(Notifications::class)
        ->call('sendNotify', [
            'title' => 'Import finished',
            'description' => 'Order 280',
            'accept' => [
                'label' => 'Open',
                'url' => $url,
            ],
        ])
        ->assertDispatched(
            'ts-ui:toast',
            fn (string $event, array $params): bool => str_contains(data_get($params, 'title', ''), 'Import finished')
        );
});
