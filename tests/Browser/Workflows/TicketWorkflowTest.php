<?php

use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;

beforeEach(function (): void {
    $this->ticketType = TicketType::factory()->create();
    $this->ticket = Ticket::factory()->create([
        'ticket_type_id' => $this->ticketType->getKey(),
        'title' => 'Browser Test Ticket',
        'authenticatable_type' => morph_alias($this->user::class),
        'authenticatable_id' => $this->user->getKey(),
    ]);
});

test('ticket list loads without js errors', function (): void {
    visit(route('tickets'))
        ->assertRoute('tickets')
        ->assertNoSmoke();
});

test('ticket list shows data table', function (): void {
    visit(route('tickets'))
        ->assertRoute('tickets')
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

test('ticket detail page loads and shows ticket number', function (): void {
    visit(route('tickets.id', ['id' => $this->ticket->getKey()]))
        ->assertNoSmoke()
        ->assertSee($this->ticket->ticket_number);
});

test('ticket detail tabs switch without errors', function (): void {
    $page = visit(route('tickets.id', ['id' => $this->ticket->getKey()]))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            if (tabs.length > 1) tabs[1].click();
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('ticket has comment editor', function (): void {
    visit(route('tickets.id', ['id' => $this->ticket->getKey()]))
        ->assertNoSmoke()
        ->assertScript('!!document.querySelector(".ProseMirror, [contenteditable]")');
});
