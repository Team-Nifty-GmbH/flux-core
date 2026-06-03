<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\States\Order\Draft;
use FluxErp\States\Ticket\InProgress;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('exposes the ticket state as its mention state', function (): void {
    $ticket = Ticket::factory()->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'state' => InProgress::class,
    ]);

    $state = $ticket->getMentionState();

    expect($state)->not->toBeNull();
    expect($state->label)->toBe(__(Str::headline((string) $ticket->state)));
    expect($state->color)->toBe($ticket->state->color());
    expect($state->color)->toBe('violet');
});

it('exposes the order state as its mention state', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order]);
    $order = Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'state' => Draft::class,
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $state = $order->getMentionState();

    expect($state)->not->toBeNull();
    expect($state->label)->toBe(__(Str::headline((string) $order->state)));
    expect($state->color)->toBe($order->state->color());
    expect($state->color)->toBe('teal');
});
