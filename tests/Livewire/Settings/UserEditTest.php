<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\UserEdit;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class UserEditTest extends TestCase
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        $language = Language::factory()->create();
        $this->actingAs(
            User::factory()->create([
                'language_id' => $language->id,
            ])
        );

        Livewire::test(UserEdit::class)
            ->assertStatus(200);
    }
}
