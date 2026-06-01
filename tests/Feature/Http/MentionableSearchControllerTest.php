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
