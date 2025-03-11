<?php

namespace FluxErp\Tests\Feature\Web\Portal;

use FluxErp\Models\Permission;

class MyProfileTest extends PortalSetup
{
    public function test_portal_my_profile_no_user(): void
    {
        $this->get(route('portal.my-profile'))
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain . '/login');
    }

    public function test_portal_my_profile_page(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('my-profile.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.my-profile'))
            ->assertStatus(200);
    }

    public function test_portal_my_profile_without_permission(): void
    {
        Permission::findOrCreate('my-profile.get', 'address');

        $this->actingAs($this->user, 'address')->get(route('portal.my-profile'))
            ->assertStatus(403);
    }
}
