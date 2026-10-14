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
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->factoryAttributes = [
        Task::class => [],
        Ticket::class => [
            'authenticatable_type' => morph_alias(User::class),
            'authenticatable_id' => $this->user->getKey(),
        ],
    ];

    $this->channelNames = fn (object $model, string $event): array => collect(Arr::wrap($model->broadcastOn($event)))
        ->map(fn (Channel $channel): string => $channel->name)
        ->all();

    $this->captureBroadcasts = function (): ArrayObject {
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
    };
});

test('a task reaches its responsible user and not another user', function (): void {
    $responsible = User::factory()->create();
    $other = User::factory()->create();

    $task = Task::factory()->make(['responsible_user_id' => $responsible->getKey()]);

    expect(($this->channelNames)($task, 'updated'))
        ->toContain('private-user.' . $responsible->getKey())
        ->not->toContain('private-user.' . $other->getKey());
});

test('a task reaches the users it is assigned to', function (): void {
    $assigned = User::factory()->create();

    $task = Task::factory()->create(['responsible_user_id' => null]);
    $task->users()->attach($assigned);

    expect(($this->channelNames)($task, 'updated'))->toContain('private-user.' . $assigned->getKey());
});

test('a task taken away from a user still reaches that user', function (): void {
    $previous = User::factory()->create();
    $next = User::factory()->create();

    $task = Task::factory()->create(['responsible_user_id' => $previous->getKey()]);
    $task->update(['responsible_user_id' => $next->getKey()]);

    expect(($this->channelNames)($task, 'updated'))->toContain(
        'private-user.' . $previous->getKey(),
        'private-user.' . $next->getKey(),
    );
});

test('assigning and unassigning a task user notifies that user', function (): void {
    $assigned = User::factory()->create();
    $task = Task::factory()->create(['responsible_user_id' => null]);

    $captured = ($this->captureBroadcasts)();

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

    expect(($this->channelNames)($ticket, 'updated'))
        ->toContain('private-user.' . $assigned->getKey())
        ->not->toContain('private-user.' . $other->getKey());
});

test('a ticket assignment reaches the assigned user', function (): void {
    $pivot = new TicketUser(['ticket_id' => 1, 'user_id' => 9]);

    expect(($this->channelNames)($pivot, 'created'))->toContain('private-user.9');
});

test('a lead reaches its user', function (): void {
    $lead = Lead::factory()->make(['user_id' => 9]);

    expect(($this->channelNames)($lead, 'updated'))->toContain('private-user.9');
});

test('an order reaches its approval user and its contact', function (string $event): void {
    $order = Order::factory()->make(['id' => 5, 'contact_id' => 3, 'approval_user_id' => 9]);

    expect(($this->channelNames)($order, $event))->toContain('private-contact.3', 'private-user.9');
})->with(['created', 'updated', 'deleted', 'locked']);

test('an order without contact and approval user reaches no personal channel', function (): void {
    $order = Order::factory()->make(['id' => 5, 'contact_id' => null, 'approval_user_id' => null]);

    expect(($this->channelNames)($order, 'updated'))->toBe([
        'private-' . morph_alias(Order::class) . '.5',
        'private-' . morph_alias(Order::class) . '.',
    ]);
});

test('a new comment reaches its record and the channel of the record model', function (): void {
    $comment = new Comment(['model_type' => morph_alias(Ticket::class), 'model_id' => 5]);

    expect(($this->channelNames)($comment, 'created'))->toBe([
        'private-' . morph_alias(Ticket::class) . '.5',
        'private-' . morph_alias(Ticket::class) . '.',
    ]);
});

test('an order moved to another contact still reaches the previous contact', function (): void {
    $order = Order::factory()->make(['id' => 5, 'contact_id' => 3, 'approval_user_id' => null]);
    $order->syncOriginal();
    $order->contact_id = 4;
    $order->syncChanges();

    expect(($this->channelNames)($order, 'updated'))->toContain('private-contact.3', 'private-contact.4');
});

test('a task saved again in the same request still notifies the user it was taken from', function (): void {
    $previous = User::factory()->create();
    $next = User::factory()->create();
    $task = Task::factory()->create(['responsible_user_id' => $previous->getKey()]);
    app(DeferredCallbackCollection::class)->invoke();

    $captured = ($this->captureBroadcasts)();

    $task->update(['responsible_user_id' => $next->getKey()]);
    $task->update(['name' => 'renamed afterwards']);
    app(DeferredCallbackCollection::class)->invoke();

    $reassignment = collect($captured->getArrayCopy())
        ->firstWhere('event', 'TaskUpdated');

    expect(data_get($reassignment, 'channels'))->toContain('private-user.' . $previous->getKey());
});

test('a new comment on any other record stays on that record', function (): void {
    $comment = new Comment(['model_type' => morph_alias(Order::class), 'model_id' => 5]);

    expect(($this->channelNames)($comment, 'created'))->toBe(['private-' . morph_alias(Order::class) . '.5']);
});

test('assigned users are only looked up when the event can concern them', function (string $model, string $event): void {
    $record = $model::factory()->create($this->factoryAttributes[$model]);
    DB::flushQueryLog();
    DB::enableQueryLog();

    ($this->channelNames)($record, $event);

    expect(collect(DB::getQueryLog())->pluck('query')->filter(fn (string $query): bool => str_contains($query, '_user')))
        ->toBeEmpty();
})->with([
    'task created' => [Task::class, 'created'],
    'task trashed' => [Task::class, 'trashed'],
    'ticket created' => [Ticket::class, 'created'],
    'ticket trashed' => [Ticket::class, 'trashed'],
]);

test('loaded assigned users are reused', function (string $model): void {
    $assigned = User::factory()->create();
    $record = $model::factory()->create($this->factoryAttributes[$model]);
    $record->users()->attach($assigned);
    $record->load('users');
    DB::flushQueryLog();
    DB::enableQueryLog();

    expect(($this->channelNames)($record, 'updated'))->toContain('private-user.' . $assigned->getKey())
        ->and(DB::getQueryLog())->toBeEmpty();
})->with([Task::class, Ticket::class]);

test('a change without broadcasting resolves no channels', function (): void {
    $task = Task::factory()->create();
    $task->users()->attach(User::factory()->create());
    app(DeferredCallbackCollection::class)->invoke();
    $captured = ($this->captureBroadcasts)();
    DB::flushQueryLog();
    DB::enableQueryLog();

    Task::withoutBroadcasting(fn () => $task->update(['name' => 'renamed quietly']));
    app(DeferredCallbackCollection::class)->invoke();

    expect(collect(DB::getQueryLog())->pluck('query')->filter(fn (string $query): bool => str_contains($query, 'task_user')))
        ->toBeEmpty()
        ->and($captured->getArrayCopy())->toBeEmpty();
});
