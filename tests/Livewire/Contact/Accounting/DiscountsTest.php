<?php

use FluxErp\Livewire\Contact\Accounting\Discounts;
use FluxErp\Models\Contact;
use FluxErp\Models\Discount;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(Discounts::class, ['contactId' => $contact->getKey()])
        ->assertOk();
});

test('sort rows updates order', function (): void {
    $contact = Contact::factory()->create();

    $discounts = Discount::factory()->count(3)->create([
        'model_type' => morph_alias(Contact::class),
        'model_id' => $contact->getKey(),
    ]);

    $contact->discounts()->attach($discounts->pluck('id'));

    $last = $discounts->last();

    Livewire::test(Discounts::class, ['contactId' => $contact->getKey()])
        ->call('sortRows', $last->getKey(), 0)
        ->assertHasNoErrors();

    expect($last->fresh()->order_column)->toBe(1);
});
