<?php

use FluxErp\Actions\SepaMandate\CreateSepaMandate;
use FluxErp\Actions\SepaMandate\DeleteSepaMandate;
use FluxErp\Actions\SepaMandate\UpdateSepaMandate;
use FluxErp\Models\Contact;

test('create sepa mandate', function (): void {
    $contact = Contact::factory()->create();

    $mandate = CreateSepaMandate::make([
        'contact_id' => $contact->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'sepa_mandate_type_enum' => 'BASIC',
    ])->validate()->execute();

    expect($mandate)->contact_id->toBe($contact->getKey());
});

test('create sepa mandate requires contact_id tenant_id type', function (): void {
    CreateSepaMandate::assertValidationErrors([], ['contact_id', 'tenant_id', 'sepa_mandate_type_enum']);
});

test('update sepa mandate', function (): void {
    $contact = Contact::factory()->create();
    $mandate = CreateSepaMandate::make([
        'contact_id' => $contact->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'sepa_mandate_type_enum' => 'BASIC',
    ])->validate()->execute();

    $updated = UpdateSepaMandate::make([
        'id' => $mandate->getKey(),
        'signed_date' => '2026-04-01',
    ])->validate()->execute();

    expect($updated->signed_date)->not->toBeNull();
});

test('delete sepa mandate', function (): void {
    $contact = Contact::factory()->create();
    $mandate = CreateSepaMandate::make([
        'contact_id' => $contact->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'sepa_mandate_type_enum' => 'BASIC',
    ])->validate()->execute();

    expect(DeleteSepaMandate::make(['id' => $mandate->getKey()])
        ->validate()->execute())->toBeTrue();
});
