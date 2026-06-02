<?php

use FluxErp\Models\Role;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Tests\Fixtures\FixtureTicketPolicy;
use Illuminate\Support\Facades\Gate;

it('returns mention candidates filtered by policy', function (): void {
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
        ->postJson('/search/mentionable', ['q' => 'Test', 'types' => ['ticket']])
        ->assertOk()
        ->assertJsonFragment(['token' => '#ticket:' . $allowed->getKey()])
        ->assertJsonMissing(['token' => '#ticket:' . $forbidden->getKey()]);
});

it('returns 422 for unknown types', function (): void {
    $u = User::factory()->create();

    $this->actingAs($u, 'web')
        ->postJson('/search/mentionable', ['q' => 'x', 'types' => ['banana']])
        ->assertStatus(422);
});

it('combines results from multiple types', function (): void {
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
        ->postJson('/search/mentionable', ['q' => 'Findme', 'types' => ['user', 'ticket']])
        ->assertOk()
        ->assertJsonCount(2);
});

it('excludes inactive users from mention candidates', function (): void {
    Role::findOrCreate('Super Admin');
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $active = User::factory()->create(['firstname' => 'Findus', 'is_active' => true]);
    User::factory()->create(['firstname' => 'Findus', 'is_active' => false]);

    $this->actingAs($admin, 'web')
        ->postJson('/search/mentionable', ['q' => 'Findus', 'types' => ['user']])
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['token' => '@user:' . $active->getKey()]);
});

it('tags record results with a record kind', function (): void {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create([
        'title' => 'Kindcheck',
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $user->id,
    ]);

    Gate::policy(Ticket::class, FixtureTicketPolicy::class);
    FixtureTicketPolicy::$allowedIds = [$ticket->getKey()];

    $this->actingAs($user, 'web')
        ->postJson('/search/mentionable', ['q' => 'Kindcheck', 'types' => ['ticket']])
        ->assertOk()
        ->assertJsonFragment(['kind' => 'record', 'token' => '#ticket:' . $ticket->getKey()]);
});

it('scopes the search to a single type via a type prefix', function (): void {
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
        ->postJson('/search/mentionable', ['q' => 'ticket:Scopeme', 'types' => ['user', 'ticket']])
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['token' => '#ticket:' . $ticket->getKey()]);
});

it('matches the type prefix case-insensitively', function (): void {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create([
        'title' => 'Caseme',
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $user->id,
    ]);

    Gate::policy(Ticket::class, FixtureTicketPolicy::class);
    FixtureTicketPolicy::$allowedIds = [$ticket->getKey()];

    $this->actingAs($user, 'web')
        ->postJson('/search/mentionable', ['q' => 'Ticket:Caseme', 'types' => ['user', 'ticket']])
        ->assertOk()
        ->assertJsonFragment(['token' => '#ticket:' . $ticket->getKey()]);
});

it('treats an unknown type prefix as a normal query', function (): void {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create([
        'title' => 'banana:Splitme',
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $user->id,
    ]);

    Gate::policy(Ticket::class, FixtureTicketPolicy::class);
    FixtureTicketPolicy::$allowedIds = [$ticket->getKey()];

    $this->actingAs($user, 'web')
        ->postJson('/search/mentionable', ['q' => 'banana:Splitme', 'types' => ['ticket']])
        ->assertOk()
        ->assertJsonFragment(['token' => '#ticket:' . $ticket->getKey()]);
});

it('returns type scope chips for an empty query with multiple types', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')
        ->postJson('/search/mentionable', ['q' => '', 'types' => ['order', 'ticket']])
        ->assertOk()
        ->assertJsonFragment(['kind' => 'scope', 'scope_key' => 'ticket'])
        ->assertJsonFragment(['kind' => 'scope', 'scope_key' => 'order']);
});

it('does not return scope chips for a single-type empty query', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')
        ->postJson('/search/mentionable', ['q' => '', 'types' => ['user']])
        ->assertOk()
        ->assertJsonCount(0);
});
