<?php

namespace FluxErp\Tests\Feature\Web\Portal;

use FluxErp\Models\Permission;

class FilesTest extends PortalSetup
{
    public function test_portal_files_no_user(): void
    {
        $this->get(route('portal.files'))
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain . '/login');
    }

    public function test_portal_files_page(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('files.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.files'))
            ->assertStatus(200);
    }

    public function test_portal_files_without_permission(): void
    {
        Permission::findOrCreate('files.get', 'address');

        $this->actingAs($this->user, 'address')->get(route('portal.files'))
            ->assertStatus(403);
    }
}
