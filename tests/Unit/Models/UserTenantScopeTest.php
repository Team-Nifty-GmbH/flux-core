<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Scopes\UserTenantScope;
use FluxErp\Models\Tenant;
use FluxErp\Models\User;

it('does not cause ambiguous column when filtering by unqualified shared column', function (): void {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create();
    $user->tenants()->attach($tenant->getKey());

    $contact = Contact::factory()
        ->hasAttached($tenant, relationship: 'tenants')
        ->create();

    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
    ]);

    $this->be($user, 'web');

    // Flush the context cache so the scope re-evaluates tenant IDs
    Illuminate\Support\Facades\Context::flush();

    // This reproduces the production error: unqualified 'contact_id' in
    // a where clause is ambiguous when the scope joins contact_tenant
    // (which also has a contact_id column)
    $result = Address::query()
        ->where('contact_id', $contact->getKey())
        ->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->getKey())->toBe($address->getKey());
});

it('does not cause ambiguous column when plucking shared column', function (): void {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create();
    $user->tenants()->attach($tenant->getKey());

    $contact = Contact::factory()
        ->hasAttached($tenant, relationship: 'tenants')
        ->create();

    Address::factory()->create([
        'contact_id' => $contact->getKey(),
    ]);

    $this->be($user, 'web');

    Illuminate\Support\Facades\Context::flush();

    $contactIds = Address::query()->pluck('contact_id');

    expect($contactIds)->toHaveCount(1)
        ->and($contactIds->first())->toBe($contact->getKey());
});

it('returns addresses for authenticated user tenant', function (): void {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create();
    $user->tenants()->attach($tenant->getKey());

    $contact = Contact::factory()
        ->hasAttached($tenant, relationship: 'tenants')
        ->create();

    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
    ]);

    $this->be($user, 'web');

    Illuminate\Support\Facades\Context::flush();

    $addresses = Address::query()->get();

    expect($addresses)->toHaveCount(1)
        ->and($addresses->first()->getKey())->toBe($address->getKey());
});

it('includes addresses without tenant assignment', function (): void {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create();
    $user->tenants()->attach($tenant->getKey());

    $contact = Contact::factory()->create();

    Address::factory()->create([
        'contact_id' => $contact->getKey(),
    ]);

    $this->be($user, 'web');

    Illuminate\Support\Facades\Context::flush();

    $addresses = Address::query()->get();

    expect($addresses)->toHaveCount(1);
});

it('does not apply scope when user is not authenticated', function (): void {
    $tenant = Tenant::factory()->create();

    $contact = Contact::factory()
        ->hasAttached($tenant, relationship: 'tenants')
        ->create();

    Address::factory()->count(3)->create([
        'contact_id' => $contact->getKey(),
    ]);

    $addresses = Address::withoutGlobalScope(UserTenantScope::class)->get();

    expect($addresses)->toHaveCount(3);
});
