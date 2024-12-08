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
            ->test(UserEdit::class)
            ->assertStatus(200);
    }

    public function test_can_save_user()
    {
        $user = User::factory()
            ->for(Language::factory())
            ->create();

        /** @var Testable $component */
        $component = Livewire::actingAs($this->user)
            ->test(UserEdit::class)
            ->call('show', $user->id)
            ->assertSuccessful()
            ->assertSet('isSuperAdmin', false)
            ->assertSet('user.id', $user->id)
            ->assertSet('user.firstname', $user->firstname)
            ->assertSet('user.lastname', $user->lastname)
            ->assertSet('user.email', $user->email)
            ->assertSet('user.user_code', $user->user_code)
            ->assertSet('user.is_active', $user->is_active)
            ->assertSet('user.password', null)
            ->assertHasNoErrors();

        $component->set('user.firstname', $newFirstName = Str::uuid()->toString())
            ->set('user.password', 'invalid')
            ->call('save')
            ->assertHasErrors(['password']);
        $this->assertEquals(4, count(data_get($component->errors()->messages(), 'password', [])));

        $component
            ->set('user.password', 'Password123!')
            ->call('save')
            ->assertHasErrors(['password'])
            ->set('user.password_confirmation', 'Password123!')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('closeModal')
            ->assertDispatchedTo('data-tables.user-list', 'loadData')
            ->assertWireuiNotification(icon: 'success');

        $this->assertEquals($newFirstName, $user->fresh()->firstname);
        $this->assertNotEquals($user->password, $user->fresh()->password);
    }
}
