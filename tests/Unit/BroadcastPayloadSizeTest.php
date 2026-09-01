<?php

use FluxErp\Models\Comment;
use FluxErp\Models\Contact;
use FluxErp\Models\QueueMonitor;

test('the queue monitor broadcast carries nothing but its key', function (): void {
    $monitor = QueueMonitor::factory()->create([
        'data' => ['payload' => str_repeat('x', 20000)],
        'exception' => str_repeat('y', 20000),
        'exception_message' => str_repeat('z', 20000),
    ]);

    expect($monitor->broadcastWith())->toBe(['model' => ['id' => $monitor->getKey()]]);
});

test('the comment broadcast carries nothing but its key', function (): void {
    $contact = Contact::factory()->create();

    $comment = Comment::factory()->create([
        'model_type' => morph_alias(Contact::class),
        'model_id' => $contact->getKey(),
        'comment' => str_repeat('x', 20000),
    ]);

    expect($comment->broadcastWith())->toBe(['model' => ['id' => $comment->getKey()]]);
});
