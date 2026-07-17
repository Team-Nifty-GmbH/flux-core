<?php

use FluxErp\Models\Notification;

function notificationWith(array $data): Notification
{
    return tap(new Notification(), fn (Notification $notification) => $notification->data = $data);
}

test('derives the menu area from the stored route name', function (): void {
    expect(notificationWith(['accept' => ['route' => 'tickets']])->menuArea())->toBe('tickets');
});

test('derives a detail route to its top level menu area', function (): void {
    expect(notificationWith(['accept' => ['route' => 'orders.id']])->menuArea())->toBe('orders');
});

test('has no menu area without a route', function (): void {
    expect(notificationWith(['accept' => ['url' => 'https://example.com']])->menuArea())->toBeNull();
});

test('has no menu area when opting out of the menu indicator', function (): void {
    expect(notificationWith(['menu_indicator' => false, 'accept' => ['route' => 'tickets']])->menuArea())->toBeNull();
});

test('exposes the full route as the menu route', function (): void {
    expect(notificationWith(['accept' => ['route' => 'accounting.payment-reminders']])->menuRoute())
        ->toBe('accounting.payment-reminders');
});

test('has no menu route without a route', function (): void {
    expect(notificationWith(['accept' => ['url' => 'https://example.com']])->menuRoute())->toBeNull();
});

test('has no menu route when opting out of the menu indicator', function (): void {
    expect(notificationWith(['menu_indicator' => false, 'accept' => ['route' => 'tickets']])->menuRoute())->toBeNull();
});
