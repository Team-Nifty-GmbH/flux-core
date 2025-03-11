<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class MyProfileTest extends BaseSetup
{
    public function test_my_profile_no_user(): void
    {
        $this->get('/my-profile')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_my_profile_page(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('my-profile.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/my-profile')
            ->assertStatus(200);
    }

    public function test_my_profile_without_permission(): void
    {
        Permission::findOrCreate('my-profile.get', 'web');

        $this->actingAs($this->user, 'web')->get('/my-profile')
            ->assertStatus(403);
    }
}
