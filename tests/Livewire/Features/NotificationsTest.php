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

function createNotification(FluxErp\Models\User $user, array $data = []): FluxErp\Models\Notification
{
    return FluxErp\Models\Notification::query()->create([
        'id' => (string) Str::uuid(),
        'type' => 'test',
        'notifiable_type' => $user->getMorphClass(),
        'notifiable_id' => $user->getKey(),
        'data' => $data,
    ]);
}

test('accept notify ignores a notification of another user', function (): void {
    $notification = createNotification(
        FluxErp\Models\User::factory()->create(),
        ['accept' => ['url' => 'https://example.com/foreign']]
    );

    Livewire::actingAs($this->user)
        ->test(Notifications::class)
        ->call('acceptNotify', $notification->getKey())
        ->assertNoRedirect();

    expect($notification->refresh()->read_at)->toBeNull();
});

test('accept notify marks an own notification as read and redirects', function (): void {
    $notification = createNotification($this->user, ['accept' => ['url' => 'https://example.com/own']]);

    Livewire::actingAs($this->user)
        ->test(Notifications::class)
        ->call('acceptNotify', $notification->getKey())
        ->assertRedirect('https://example.com/own');

    expect($notification->refresh()->read_at)->not->toBeNull();
});

test('mark as read ignores a notification of another user', function (): void {
    $notification = createNotification(FluxErp\Models\User::factory()->create());

    Livewire::actingAs($this->user)
        ->test(Notifications::class)
        ->call('markAsRead', $notification->getKey());

    expect($notification->refresh()->read_at)->toBeNull();
});

test('mark as read marks an own notification as read', function (): void {
    $notification = createNotification($this->user);

    Livewire::actingAs($this->user)
        ->test(Notifications::class)
        ->call('markAsRead', $notification->getKey());

    expect($notification->refresh()->read_at)->not->toBeNull();
});

test('send notify ignores a notification of another user', function (): void {
    $notification = createNotification(
        FluxErp\Models\User::factory()->create(),
        ['title' => 'Foreign secret', 'accept' => ['url' => 'https://example.com/foreign']]
    );

    Livewire::actingAs($this->user)
        ->test(Notifications::class)
        ->call('sendNotify', [
            'id' => $notification->getKey(),
            'markAsRead' => true,
            'accept' => ['url' => 'https://example.com/attacker'],
        ])
        ->assertDontSee('Foreign secret');

    expect($notification->refresh()->read_at)->toBeNull();
});

test('send notify marks an own notification as read', function (): void {
    $notification = createNotification($this->user);

    Livewire::actingAs($this->user)
        ->test(Notifications::class)
        ->call('sendNotify', [
            'id' => $notification->getKey(),
            'markAsRead' => true,
            'accept' => ['url' => 'https://example.com/own'],
        ]);

    expect($notification->refresh()->read_at)->not->toBeNull();
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
