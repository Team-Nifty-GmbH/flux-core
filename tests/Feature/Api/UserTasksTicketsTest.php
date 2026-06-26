<?php

use FluxErp\Models\Permission;
use FluxErp\Models\Task;
use FluxErp\Models\Ticket;
use FluxErp\States\Task\Done as TaskDone;
use FluxErp\States\Task\Open as TaskOpen;
use FluxErp\States\Ticket\Closed as TicketClosed;
use FluxErp\States\Ticket\InProgress as TicketInProgress;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->tasksPermission = Permission::findOrCreate('api.user.tasks.get', 'sanctum');
    $this->ticketsPermission = Permission::findOrCreate('api.user.tickets.get', 'sanctum');
});

test('the user tasks endpoint returns open tasks the user is responsible for or assigned to', function (): void {
    $mineResponsible = Task::factory()->create(['responsible_user_id' => $this->user->id, 'state' => TaskOpen::class]);
    $mineAssigned = Task::factory()->create(['state' => TaskOpen::class]);
    $mineAssigned->users()->attach($this->user->id);
    $doneMine = Task::factory()->create(['responsible_user_id' => $this->user->id, 'state' => TaskDone::class]);
    $someoneElse = Task::factory()->create(['state' => TaskOpen::class]);

    $this->user->givePermissionTo($this->tasksPermission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->getJson('/api/user/tasks')->assertOk();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($mineResponsible->id, $mineAssigned->id);
    expect($ids)->not->toContain($doneMine->id);
    expect($ids)->not->toContain($someoneElse->id);
    expect($response->json('data.0'))->toHaveKeys(['id', 'name', 'state', 'url']);
});

test('the user tickets endpoint returns open tickets assigned to the user', function (): void {
    $authenticatable = [
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
    ];
    $mine = Ticket::factory()->create(['state' => TicketInProgress::class, ...$authenticatable]);
    $mine->users()->attach($this->user->id);
    $closedMine = Ticket::factory()->create(['state' => TicketClosed::class, ...$authenticatable]);
    $closedMine->users()->attach($this->user->id);
    $someoneElse = Ticket::factory()->create(['state' => TicketInProgress::class, ...$authenticatable]);

    $this->user->givePermissionTo($this->ticketsPermission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->getJson('/api/user/tickets')->assertOk();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($mine->id);
    expect($ids)->not->toContain($closedMine->id, $someoneElse->id);
    expect($response->json('data.0'))->toHaveKeys(['id', 'ticket_number', 'title', 'state', 'url']);
});
