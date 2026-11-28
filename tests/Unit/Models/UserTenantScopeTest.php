<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Scopes\UserTenantScope;
use FluxErp\Models\Tenant;
use FluxErp\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;

test('does not cause ambiguous column when filtering by unqualified shared column', function (): void {
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
    Context::flush();

    // This reproduces the production error: unqualified 'contact_id' in
    // a where clause is ambiguous when the scope joins contact_tenant
    // (which also has a contact_id column)
    $result = Address::query()
        ->where('contact_id', $contact->getKey())
        ->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->getKey())->toBe($address->getKey());
});

test('does not cause ambiguous column when plucking shared column', function (): void {
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

    Context::flush();

    $contactIds = Address::query()->pluck('contact_id');

    expect($contactIds)->toHaveCount(1)
        ->and($contactIds->first())->toBe($contact->getKey());
});

test('returns addresses for authenticated user tenant', function (): void {
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

    Context::flush();

    $addresses = Address::query()->get();

    expect($addresses)->toHaveCount(1)
        ->and($addresses->first()->getKey())->toBe($address->getKey());
});

test('includes addresses without tenant assignment', function (): void {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create();
    $user->tenants()->attach($tenant->getKey());

    $contact = Contact::factory()->create();

    Address::factory()->create([
        'contact_id' => $contact->getKey(),
    ]);

    $this->be($user, 'web');

    Context::flush();

    $addresses = Address::query()->get();

    expect($addresses)->toHaveCount(1);
});

test('does not apply scope when user is not authenticated', function (): void {
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

test('filters by the tenants of the user set later in the same request', function (): void {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA = User::factory()->create();
    $userA->tenants()->attach($tenantA->getKey());
    $userB = User::factory()->create();
    $userB->tenants()->attach($tenantB->getKey());

    $contactA = Contact::factory()->hasAttached($tenantA, relationship: 'tenants')->create();
    $contactB = Contact::factory()->hasAttached($tenantB, relationship: 'tenants')->create();

    Context::flush();

    $this->be($userA, 'web');
    expect(Contact::query()->pluck('id')->all())->toBe([$contactA->getKey()]);

    Auth::setUser($userB);
    expect(Contact::query()->pluck('id')->all())->toBe([$contactB->getKey()]);
});

test('filters by the tenants of a later user when the first user has no tenants', function (): void {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $userB->tenants()->attach($tenantB->getKey());

    Contact::factory()->hasAttached($tenantA, relationship: 'tenants')->create();
    $contactB = Contact::factory()->hasAttached($tenantB, relationship: 'tenants')->create();

    Context::flush();

    $this->be($userA, 'web');
    expect(Contact::query()->count())->toBe(2);

    Auth::setUser($userB);
    expect(Contact::query()->pluck('id')->all())->toBe([$contactB->getKey()]);
});

test('a queued job running as another user does not reuse the tenants of the dispatching user', function (): void {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA = User::factory()->create();
    $userA->tenants()->attach($tenantA->getKey());
    $userB = User::factory()->create();
    $userB->tenants()->attach($tenantB->getKey());

    Contact::factory()->hasAttached($tenantA, relationship: 'tenants')->create();
    $contactB = Contact::factory()->hasAttached($tenantB, relationship: 'tenants')->create();

    Context::flush();

    $this->be($userA, 'web');
    Contact::query()->get();

    $payload = Context::dehydrate();
    Context::flush();
    Context::hydrate($payload);

    Auth::setUser($userB);
    expect(Contact::query()->pluck('id')->all())->toBe([$contactB->getKey()]);
});
