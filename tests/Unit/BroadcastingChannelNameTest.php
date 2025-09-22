<?php

use FluxErp\Actions\ContactBankConnection\UpdateContactBankConnection;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Order;
use FluxErp\Models\Product;
use Illuminate\Support\Str;

it('uses morph alias for model broadcast channel', function (): void {
    $order = new Order();
    $order->id = 123;

    $broadcastChannel = $order->broadcastChannel();
    $expectedChannel = morph_alias(Order::class) . '.123';

    expect($broadcastChannel)->toBe($expectedChannel);
});

it('uses class_to_broadcast_channel helper for action broadcast channel', function (): void {
    $testClass = CreateOrder::class;

    $expectedChannel = 'action.' . Str::of(morph_alias($testClass))
        ->replace('\\', '.')
        ->lower()
        ->toString();

    $actualChannel = 'action.' . class_to_broadcast_channel($testClass, false);

    expect($actualChannel)->toBe($expectedChannel);
});

it('uses morph alias in class_to_broadcast_channel without param', function (): void {
    $testClass = Product::class;

    $channel = class_to_broadcast_channel($testClass, false);
    $expected = Str::of(morph_alias($testClass))
        ->replace('\\', '.')
        ->lower()
        ->toString();

    expect($channel)->toBe($expected);
});

it('appends camelCase class basename with param', function (): void {
    $testClass = Product::class;

    $channel = class_to_broadcast_channel($testClass, true);
    $expected = Str::of(morph_alias($testClass))
        ->replace('\\', '.')
        ->lower()
        ->toString() . '.{product}';

    expect($channel)->toBe($expected);
});

it('handles different models with param correctly', function (): void {
    $orderClass = Order::class;

    $orderChannelWithParam = class_to_broadcast_channel($orderClass, true);
    $expectedOrder = Str::of(morph_alias($orderClass))
        ->replace('\\', '.')
        ->lower()
        ->toString() . '.{order}';

    expect($orderChannelWithParam)->toBe($expectedOrder);

    $bankClass = ContactBankConnection::class;

    $bankChannelWithParam = class_to_broadcast_channel($bankClass, true);
    $expectedBank = Str::of(morph_alias($bankClass))
        ->replace('\\', '.')
        ->lower()
        ->toString() . '.{contactBankConnection}';

    expect($bankChannelWithParam)->toBe($expectedBank);
});

it('handles actions with param correctly', function (): void {
    $actionClass = CreateOrder::class;

    $channelWithParam = class_to_broadcast_channel($actionClass, true);
    $expected = Str::of(morph_alias($actionClass))
        ->replace('\\', '.')
        ->lower()
        ->toString() . '.{createOrder}';

    expect($channelWithParam)->toBe($expected);

    $updateAction = UpdateContactBankConnection::class;

    $updateChannelWithParam = class_to_broadcast_channel($updateAction, true);
    $expectedUpdate = Str::of(morph_alias($updateAction))
        ->replace('\\', '.')
        ->lower()
        ->toString() . '.{updateContactBankConnection}';

    expect($updateChannelWithParam)->toBe($expectedUpdate);
});
