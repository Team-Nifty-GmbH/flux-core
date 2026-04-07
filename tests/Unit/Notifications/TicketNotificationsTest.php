<?php

use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Notifications\Ticket\TicketAssignedNotification;
use FluxErp\Notifications\Ticket\TicketCreatedNotification;
use FluxErp\Notifications\Ticket\TicketUpdatedNotification;
use function Livewire\invade;

beforeEach(function (): void {
    $ticketType = TicketType::factory()->create();
    $this->ticket = Ticket::factory()->create([
        'ticket_type_id' => $ticketType->getKey(),
        'title' => 'Notification Test Ticket',
        'authenticatable_type' => morph_alias($this->user::class),
        'authenticatable_id' => $this->user->getKey(),
    ]);
});

test('ticket created notification has support icon', function (): void {
    $notification = new TicketCreatedNotification();
    $notification->model = $this->ticket;

    expect(invade($notification)->getNotificationIcon())->toBe('support');
});

test('ticket updated notification has support icon', function (): void {
    $notification = new TicketUpdatedNotification();
    $notification->model = $this->ticket;

    expect(invade($notification)->getNotificationIcon())->toBe('support');
});

test('ticket assigned notification has support icon', function (): void {
    $notification = new TicketAssignedNotification();
    $notification->model = $this->ticket;

    expect(invade($notification)->getNotificationIcon())->toBe('support');
});

test('ticket created notification has title', function (): void {
    $notification = new TicketCreatedNotification();
    $notification->model = $this->ticket;

    expect(invade($notification)->getTitle())->toBeString()->not->toBeEmpty();
});
