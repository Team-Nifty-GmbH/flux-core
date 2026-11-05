<?php

use FluxErp\Livewire\Features\Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Features\SupportTesting\Testable;
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

test('mark all as read updates only own unread notifications in a single query', function (): void {
    $unread = collect(range(1, 3))->map(fn (): FluxErp\Models\Notification => createNotification($this->user));

    $alreadyRead = createNotification($this->user);
    $alreadyRead->forceFill(['read_at' => now()->subWeek()])->save();
    $readAt = $alreadyRead->refresh()->read_at;

    $foreign = createNotification(FluxErp\Models\User::factory()->create());

    $component = Livewire::actingAs($this->user)->test(Notifications::class);

    DB::enableQueryLog();
    DB::flushQueryLog();

    $component->call('markAllAsRead')
        ->assertSet('unread', 0)
        ->assertDispatched('notifications-changed');

    $updates = collect(DB::getQueryLog())
        ->filter(
            fn (array $query): bool => str_starts_with(strtolower(data_get($query, 'query')), 'update `notifications`')
        );

    expect($updates)->toHaveCount(1)
        ->and($this->user->unreadNotifications()->count())->toBe(0)
        ->and($unread->every(
            fn (FluxErp\Models\Notification $notification): bool => ! is_null($notification->refresh()->read_at)
        ))->toBeTrue()
        ->and($alreadyRead->refresh()->read_at->equalTo($readAt))->toBeTrue()
        ->and($foreign->refresh()->read_at)->toBeNull();
});

test('mark all as read button shows a spinner while the request runs', function (): void {
    Livewire::actingAs($this->user)
        ->test(Notifications::class)
        ->assertSeeHtml('wire:target="markAllAsRead"');
});

function shownNotificationIds(Testable $component): array
{
    $component->call('showNotifications');

    return collect(data_get($component->effects, 'dispatches'))
        ->where('name', 'ts-ui:toast-list')
        ->map(fn (array $dispatch): ?string => data_get($dispatch, 'params.hooks.close.params'))
        ->values()
        ->all();
}

test('show notifications loads every unread notification once in pages of twenty', function (): void {
    $expected = collect(range(1, 65))
        ->map(function (int $minutes): string {
            $notification = createNotification($this->user);
            $notification->forceFill(['created_at' => now()->subMinutes($minutes)])->save();

            return $notification->getKey();
        });

    $component = Livewire::actingAs($this->user)->test(Notifications::class);

    $pages = collect(range(1, 4))
        ->map(fn (): array => shownNotificationIds($component));

    expect($pages->map(fn (array $page): int => count($page))->all())->toBe([20, 20, 20, 5])
        ->and($pages->flatten()->all())->toBe($expected->all());
});

test('closing a notification does not skip one on the next page', function (): void {
    $expected = collect(range(1, 25))
        ->map(function (int $minutes): string {
            $notification = createNotification($this->user);
            $notification->forceFill(['created_at' => now()->subMinutes($minutes)])->save();

            return $notification->getKey();
        });

    $component = Livewire::actingAs($this->user)->test(Notifications::class);

    expect(shownNotificationIds($component))->toBe($expected->take(20)->all());

    $component->call('markAsRead', $expected->first());

    expect(shownNotificationIds($component))->toBe($expected->slice(20)->values()->all());
});

test('notifications with the same timestamp are each loaded once', function (): void {
    $createdAt = now()->subHour();
    $expected = collect(range(1, 65))
        ->map(function () use ($createdAt): string {
            $notification = createNotification($this->user);
            $notification->forceFill(['created_at' => $createdAt])->save();

            return $notification->getKey();
        });

    $component = Livewire::actingAs($this->user)->test(Notifications::class);

    $shown = collect(range(1, 4))
        ->flatMap(fn (): array => shownNotificationIds($component));

    expect($shown->sort()->values()->all())->toBe($expected->sort()->values()->all());
});

test('show notifications stops at the end and restarts after closing the slide', function (): void {
    $notification = createNotification($this->user);

    $component = Livewire::actingAs($this->user)->test(Notifications::class);

    expect(shownNotificationIds($component))->toBe([$notification->getKey()])
        ->and(shownNotificationIds($component))->toBe([]);

    $component->call('closeNotifications');

    expect(shownNotificationIds($component))->toBe([$notification->getKey()]);
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
