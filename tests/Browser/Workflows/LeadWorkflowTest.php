<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadState;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();
    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);
    $this->leadState = LeadState::factory()->create();
    $this->lead = Lead::factory()->create([
        'address_id' => $this->address->getKey(),
        'lead_state_id' => $this->leadState->getKey(),
    ]);
});

test('lead list loads without js errors', function (): void {
    visit(route('sales.leads'))
        ->assertRoute('sales.leads')
        ->assertNoSmoke();
});

test('lead list shows data table', function (): void {
    visit(route('sales.leads'))
        ->assertRoute('sales.leads')
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

test('lead detail page loads', function (): void {
    visit(route('sales.lead.id', ['id' => $this->lead->getKey()]))
        ->assertNoSmoke();
});

test('lead detail tabs switch without errors', function (): void {
    $page = visit(route('sales.lead.id', ['id' => $this->lead->getKey()]))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            if (tabs.length > 1) tabs[1].click();
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('lead create modal opens', function (): void {
    visit(route('sales.leads'))
        ->assertRoute('sales.leads')
        ->assertNoSmoke()
        ->click('New')
        ->assertNoJavascriptErrors();
});
