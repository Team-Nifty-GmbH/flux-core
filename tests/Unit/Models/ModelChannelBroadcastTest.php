<?php

use FluxErp\Models\Address;
use FluxErp\Models\Comment;
use FluxErp\Models\Order;
use Illuminate\Support\Arr;

function broadcastChannelNames(object $model, string $event): array
{
    return collect(Arr::wrap($model->broadcastOn($event)))
        ->map(fn ($channel): string => $channel->name)
        ->all();
}

test('model events reach the record channel and the channel of the model', function (string $event): void {
    $order = Order::factory()->make(['id' => 5]);

    expect(broadcastChannelNames($order, $event))->toBe([
        'private-' . morph_alias(Order::class) . '.5',
        'private-' . morph_alias(Order::class) . '.',
    ]);
})->with(['updated', 'deleted', 'trashed', 'restored']);

test('a created event reaches only the channel of the model', function (): void {
    $order = Order::factory()->make(['id' => 5]);

    expect(broadcastChannelNames($order, 'created'))->toBe(['private-' . morph_alias(Order::class) . '.']);
});

test('an address also reaches the channel of addresses and of its contact', function (): void {
    $address = Address::factory()->make(['id' => 7, 'contact_id' => 3]);

    expect(broadcastChannelNames($address, 'updated'))->toBe([
        'private-' . morph_alias(Address::class) . '.7',
        'private-' . morph_alias(Address::class) . '.',
        'private-contact.3',
    ]);
});

test('a comment stays on the channel of the record it belongs to', function (): void {
    $comment = new Comment(['model_type' => morph_alias(Order::class), 'model_id' => 5]);

    expect(broadcastChannelNames($comment, 'updated'))->toBe(['private-' . morph_alias(Order::class) . '.5']);
});
