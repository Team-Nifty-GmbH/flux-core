<?php

use FluxErp\Models\Comment;
use FluxErp\Models\Contact;
use FluxErp\Models\QueueMonitor;

test('the queue monitor broadcast leaves out the unbounded columns', function (): void {
    $monitor = QueueMonitor::factory()->create([
        'data' => ['payload' => str_repeat('x', 20000)],
        'exception' => str_repeat('y', 20000),
        'exception_message' => str_repeat('z', 20000),
    ]);

    $payload = $monitor->broadcastWith()['model'];

    expect($payload)->not->toHaveKeys(['exception', 'exception_message', 'data', 'accept', 'reject'])
        ->and($payload)->toHaveKeys(['id', 'name', 'state'])
        ->and(strlen(json_encode($payload)))->toBeLessThan(10240);
});

test('the comment broadcast leaves out the comment body', function (): void {
    $contact = Contact::factory()->create();

    $comment = Comment::factory()->create([
        'model_type' => morph_alias(Contact::class),
        'model_id' => $contact->getKey(),
        'comment' => str_repeat('x', 20000),
    ]);

    $payload = $comment->broadcastWith()['model'];

    expect($payload)->not->toHaveKey('comment')
        ->and($payload)->toHaveKey('id')
        ->and(strlen(json_encode($payload)))->toBeLessThan(10240);
});
