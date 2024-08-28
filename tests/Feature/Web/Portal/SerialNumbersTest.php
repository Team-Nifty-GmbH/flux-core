<?php

namespace FluxErp\Tests\Feature\Web\Portal;

use FluxErp\Models\Permission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SerialNumbersTest extends PortalSetup
{
    use DatabaseTransactions;

    public function test_portal_serial_numbers_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('serial-numbers.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.serial-numbers'))
            ->assertStatus(200);
    }

    public function test_portal_serial_numbers_no_user()
    {
        $this->get(route('portal.serial-numbers'))
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain . '/login');
    }

    public function test_portal_serial_numbers_without_permission()
    {
        Permission::findOrCreate('serial-numbers.get', 'address');

        $this->actingAs($this->user, 'address')->get(route('portal.serial-numbers'))
            ->assertStatus(403);
    }
}
