<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\UserEdit;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Str;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

class UserEditTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::actingAs($this->user)
            ->test(UserEdit::class, ['user' => $this->user])
            ->assertStatus(200);
    }

    public function test_can_save_user()
    {
        $user = User::factory()
            ->for(Language::factory())
            ->create();

        /** @var Testable $component */
        $component = Livewire::actingAs($this->user)
            ->test(UserEdit::class, ['user' => $user])
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertSet('isSuperAdmin', false)
            ->assertSet('userForm.id', $user->id)
            ->assertSet('userForm.firstname', $user->firstname)
            ->assertSet('userForm.lastname', $user->lastname)
            ->assertSet('userForm.email', $user->email)
            ->assertSet('userForm.user_code', $user->user_code)
            ->assertSet('userForm.is_active', $user->is_active)
            ->assertSet('userForm.password', null);

        $component->set('userForm.firstname', $newFirstName = Str::uuid()->toString())
            ->set('userForm.password', 'invalid')
            ->call('save')
            ->assertHasErrors(['password']);
        $this->assertEquals(4, count(data_get($component->errors()->messages(), 'password', [])));

        $component
            ->set('userForm.password', 'Password123!')
            ->call('save')
            ->assertHasErrors(['password'])
            ->set('userForm.password_confirmation', 'Password123!')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('closeModal')
            ->assertDispatchedTo('data-tables.user-list', 'loadData')
            ->assertWireuiNotification(icon: 'success');

        $this->assertEquals($newFirstName, $user->fresh()->firstname);
        $this->assertNotEquals($user->password, $user->fresh()->password);
    }
}
