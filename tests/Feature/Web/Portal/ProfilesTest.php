<?php

namespace FluxErp\Tests\Feature\Web\Portal;

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProfilesTest extends PortalSetup
{
    use DatabaseTransactions;

    public function test_portal_profiles_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('profiles.{id?}.get', 'address'));

        $this->actingAs($this->user, 'address')->get(
            route('portal.profiles.id?', ['id' => $this->user->id])
        )
            ->assertStatus(200);
    }

    public function test_portal_profiles_new_profile()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('profiles.{id?}.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.profiles.id?', ['id' => 'new']))
            ->assertStatus(200);
    }

    public function test_portal_profiles_without_id()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('profiles.{id?}.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.profiles.id?', ['id' => null]))
            ->assertStatus(200);
    }

    public function test_portal_profiles_no_user()
    {
        $this->get(route('portal.profiles.id?', ['id' => $this->user->id]))
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain . '/login');
    }

    public function test_portal_profiles_without_permission()
    {
        $this->actingAs($this->user, 'address')->get(
            route('portal.profiles.id?', ['id' => $this->user->id])
        )
            ->assertStatus(403);
    }

    public function test_portal_profiles_address_not_found()
    {
        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $address = Address::factory()->create([
            'contact_id' => $contact->id,
            'client_id' => $this->dbClient->id,
            'language_id' => $this->user->language_id,
        ]);

        $this->user->givePermissionTo(Permission::findOrCreate('profiles.{id?}.get', 'address'));

        $this->actingAs($this->user, 'address')->get(
            route('portal.profiles.id?', ['id' => $address->id])
        )
            ->assertStatus(404);
    }
}
