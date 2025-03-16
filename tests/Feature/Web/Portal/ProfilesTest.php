<?php

namespace FluxErp\Tests\Feature\Web\Portal;

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;

class ProfilesTest extends PortalSetup
{
    public function test_portal_profiles_address_not_found(): void
    {
        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $address = Address::factory()->create([
            'contact_id' => $contact->id,
            'client_id' => $this->dbClient->getKey(),
            'language_id' => $this->user->language_id,
        ]);

        $this->user->givePermissionTo(Permission::findOrCreate('profiles.{id?}.get', 'address'));

        $this->actingAs($this->user, 'address')->get(
            route('portal.profiles.id?', ['id' => $address->id])
        )
            ->assertStatus(404);
    }

    public function test_portal_profiles_new_profile(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('profiles.{id?}.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.profiles.id?', ['id' => 'new']))
            ->assertStatus(200);
    }

    public function test_portal_profiles_no_user(): void
    {
        $this->get(route('portal.profiles.id?', ['id' => $this->user->id]))
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain . '/login');
    }

    public function test_portal_profiles_page(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('profiles.{id?}.get', 'address'));

        $this->actingAs($this->user, 'address')->get(
            route('portal.profiles.id?', ['id' => $this->user->id])
        )
            ->assertStatus(200);
    }

    public function test_portal_profiles_without_id(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('profiles.{id?}.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.profiles.id?', ['id' => null]))
            ->assertStatus(200);
    }

    public function test_portal_profiles_without_permission(): void
    {
        Permission::findOrCreate('profiles.{id?}.get', 'address');

        $this->actingAs($this->user, 'address')->get(
            route('portal.profiles.id?', ['id' => $this->user->id])
        )
            ->assertStatus(403);
    }
}
