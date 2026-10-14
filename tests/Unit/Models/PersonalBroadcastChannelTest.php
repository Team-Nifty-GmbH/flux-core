<?php

use FluxErp\Models\Comment;
use FluxErp\Models\Lead;
use FluxErp\Models\Order;
use FluxErp\Models\Pivots\TicketUser;
use FluxErp\Models\Task;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\Broadcaster;
use Illuminate\Support\Arr;
use Illuminate\Support\Defer\DeferredCallbackCollection;
use Illuminate\Support\Facades\Broadcast;

function personalChannelNames(object $model, string $event): array
{
    return collect(Arr::wrap($model->broadcastOn($event)))
        ->map(fn (Channel $channel): string => $channel->name)
        ->all();
}

function captureModelBroadcasts(): ArrayObject
{
    $captured = new ArrayObject();

    Broadcast::extend('capture', fn (): Broadcaster => new class($captured) implements Broadcaster
    {
        public function __construct(protected ArrayObject $captured) {}

        public function auth($request): void {}

        public function validAuthenticationResponse($request, $result): void {}

        public function broadcast(array $channels, $event, array $payload = []): void
        {
            $this->captured[] = [
                'channels' => array_values(array_map('strval', $channels)),
                'event' => $event,
                'payload' => $payload,
            ];
        }
    });

    config()->set('broadcasting.connections.capture', ['driver' => 'capture']);
    config()->set('broadcasting.default', 'capture');

    return $captured;
}

test('a task reaches its responsible user and not another user', function (): void {
    $responsible = User::factory()->create();
    $other = User::factory()->create();

    $task = Task::factory()->make(['responsible_user_id' => $responsible->getKey()]);

    expect(personalChannelNames($task, 'updated'))
        ->toContain('private-user.' . $responsible->getKey())
        ->not->toContain('private-user.' . $other->getKey());
});

test('a task reaches the users it is assigned to', function (): void {
    $assigned = User::factory()->create();

    $task = Task::factory()->create(['responsible_user_id' => null]);
    $task->users()->attach($assigned);

    expect(personalChannelNames($task, 'updated'))->toContain('private-user.' . $assigned->getKey());
});

test('a task taken away from a user still reaches that user', function (): void {
    $previous = User::factory()->create();
    $next = User::factory()->create();

    $task = Task::factory()->create(['responsible_user_id' => $previous->getKey()]);
    $task->update(['responsible_user_id' => $next->getKey()]);

    expect(personalChannelNames($task, 'updated'))->toContain(
        'private-user.' . $previous->getKey(),
        'private-user.' . $next->getKey(),
    );
});

test('assigning and unassigning a task user notifies that user', function (): void {
    $assigned = User::factory()->create();
    $task = Task::factory()->create(['responsible_user_id' => null]);

    $captured = captureModelBroadcasts();

    $task->users()->attach($assigned);
    $task->users()->detach($assigned);
    app(DeferredCallbackCollection::class)->invoke();

    $toUser = collect($captured->getArrayCopy())
        ->filter(fn (array $broadcast): bool => in_array(
            'private-user.' . $assigned->getKey(),
            $broadcast['channels']
        ))
        ->pluck('event')
        ->all();

    expect($toUser)->toContain('TaskUserCreated', 'TaskUserDeleted');
});

test('a ticket reaches the users it is assigned to', function (): void {
    $assigned = User::factory()->create();
    $other = User::factory()->create();

    $ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $other->getKey(),
    ]);
    $ticket->users()->attach($assigned);

    expect(personalChannelNames($ticket, 'updated'))
        ->toContain('private-user.' . $assigned->getKey())
        ->not->toContain('private-user.' . $other->getKey());
});

test('a ticket assignment reaches the assigned user', function (): void {
    $pivot = new TicketUser(['ticket_id' => 1, 'user_id' => 9]);

    expect(personalChannelNames($pivot, 'created'))->toContain('private-user.9');
});

test('a lead reaches its user', function (): void {
    $lead = Lead::factory()->make(['user_id' => 9]);

    expect(personalChannelNames($lead, 'updated'))->toContain('private-user.9');
});

test('an order reaches its approval user and its contact', function (string $event): void {
    $order = Order::factory()->make(['id' => 5, 'contact_id' => 3, 'approval_user_id' => 9]);

    expect(personalChannelNames($order, $event))->toContain('private-contact.3', 'private-user.9');
})->with(['created', 'updated', 'deleted', 'locked']);

test('an order without contact and approval user reaches no personal channel', function (): void {
    $order = Order::factory()->make(['id' => 5, 'contact_id' => null, 'approval_user_id' => null]);

    expect(personalChannelNames($order, 'updated'))->toBe([
        'private-' . morph_alias(Order::class) . '.5',
        'private-' . morph_alias(Order::class) . '.',
    ]);
});

test('a new comment reaches its record and the channel of the record model', function (): void {
    $comment = new Comment(['model_type' => morph_alias(Ticket::class), 'model_id' => 5]);

    expect(personalChannelNames($comment, 'created'))->toBe([
        'private-' . morph_alias(Ticket::class) . '.5',
        'private-' . morph_alias(Ticket::class) . '.',
    ]);
});
