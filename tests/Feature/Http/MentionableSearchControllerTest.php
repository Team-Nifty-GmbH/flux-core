<?php

use FluxErp\Models\Role;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Tests\Fixtures\FixtureTicketPolicy;
use Illuminate\Support\Facades\Gate;

test('returns mention candidates filtered by policy', function (): void {
    $user = User::factory()->create();
    $allowed = Ticket::factory()->create([
        'title' => 'Allowed Test',
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $user->id,
    ]);
    $forbidden = Ticket::factory()->create([
        'title' => 'Forbidden Test',
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $user->id,
    ]);

    Gate::policy(Ticket::class, FixtureTicketPolicy::class);
    FixtureTicketPolicy::$allowedIds = [$allowed->getKey()];

    $this->actingAs($user, 'web')
        ->postJson('/search/mentionable', ['query' => 'Test', 'types' => ['ticket']])
        ->assertOk()
        ->assertJsonFragment(['token' => '#ticket:' . $allowed->getKey()])
        ->assertJsonMissing(['token' => '#ticket:' . $forbidden->getKey()]);
});

test('skips a mentionable type whose search throws without failing the request', function (): void {
    Role::findOrCreate('Super Admin');
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $ticket = Ticket::factory()->create([
        'title' => 'Findme Ticket',
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $admin->id,
    ]);

    FluxErp\Tests\Fixtures\ThrowingMentionFixture::register('throwing_fixture');

    $this->actingAs($admin, 'web')
        ->postJson('/search/mentionable', ['query' => 'Findme', 'types' => ['ticket', 'throwing_fixture']])
        ->assertOk()
        ->assertJsonFragment(['token' => '#ticket:' . $ticket->getKey()]);
});

test('returns 422 for unknown types', function (): void {
    $u = User::factory()->create();

    $this->actingAs($u, 'web')
        ->postJson('/search/mentionable', ['query' => 'x', 'types' => ['banana']])
        ->assertStatus(422);
});

test('combines results from multiple types', function (): void {
    Role::findOrCreate('Super Admin');
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    User::factory()->create(['firstname' => 'Findme', 'is_active' => true]);
    Ticket::factory()->create([
        'title' => 'Findme Ticket',
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $admin->id,
    ]);

    $this->actingAs($admin, 'web')
        ->postJson('/search/mentionable', ['query' => 'Findme', 'types' => ['user', 'ticket']])
        ->assertOk()
        ->assertJsonCount(2);
});

test('excludes inactive users from mention candidates', function (): void {
    Role::findOrCreate('Super Admin');
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $active = User::factory()->create(['firstname' => 'Findus', 'is_active' => true]);
    User::factory()->create(['firstname' => 'Findus', 'is_active' => false]);

    $this->actingAs($admin, 'web')
        ->postJson('/search/mentionable', ['query' => 'Findus', 'types' => ['user']])
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['token' => '@user:' . $active->getKey()]);
});

test('tags record results with a record kind', function (): void {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create([
        'title' => 'Kindcheck',
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $user->id,
    ]);

    Gate::policy(Ticket::class, FixtureTicketPolicy::class);
    FixtureTicketPolicy::$allowedIds = [$ticket->getKey()];

    $this->actingAs($user, 'web')
        ->postJson('/search/mentionable', ['query' => 'Kindcheck', 'types' => ['ticket']])
        ->assertOk()
        ->assertJsonFragment(['kind' => 'record', 'token' => '#ticket:' . $ticket->getKey()]);
});

test('scopes the search to a single type via a type prefix', function (): void {
    Role::findOrCreate('Super Admin');
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    User::factory()->create(['firstname' => 'Scopeme', 'is_active' => true]);
    $ticket = Ticket::factory()->create([
        'title' => 'Scopeme Ticket',
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $admin->id,
    ]);

    $this->actingAs($admin, 'web')
        ->postJson('/search/mentionable', ['query' => 'ticket:Scopeme', 'types' => ['user', 'ticket']])
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['token' => '#ticket:' . $ticket->getKey()]);
});

test('matches the type prefix case-insensitively', function (): void {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create([
        'title' => 'Caseme',
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $user->id,
    ]);

    Gate::policy(Ticket::class, FixtureTicketPolicy::class);
    FixtureTicketPolicy::$allowedIds = [$ticket->getKey()];

    $this->actingAs($user, 'web')
        ->postJson('/search/mentionable', ['query' => 'Ticket:Caseme', 'types' => ['user', 'ticket']])
        ->assertOk()
        ->assertJsonFragment(['token' => '#ticket:' . $ticket->getKey()]);
});

test('treats an unknown type prefix as a normal query', function (): void {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create([
        'title' => 'banana:Splitme',
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $user->id,
    ]);

    Gate::policy(Ticket::class, FixtureTicketPolicy::class);
    FixtureTicketPolicy::$allowedIds = [$ticket->getKey()];

    $this->actingAs($user, 'web')
        ->postJson('/search/mentionable', ['query' => 'banana:Splitme', 'types' => ['ticket']])
        ->assertOk()
        ->assertJsonFragment(['token' => '#ticket:' . $ticket->getKey()]);
});

test('returns type scope chips for an empty query with multiple types', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')
        ->postJson('/search/mentionable', ['query' => '', 'types' => ['order', 'ticket']])
        ->assertOk()
        ->assertJsonFragment(['kind' => 'scope', 'scope_key' => 'ticket'])
        ->assertJsonFragment(['kind' => 'scope', 'scope_key' => 'order']);
});

test('does not return scope chips for a single-type empty query', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')
        ->postJson('/search/mentionable', ['query' => '', 'types' => ['user']])
        ->assertOk()
        ->assertJsonCount(0);
});
