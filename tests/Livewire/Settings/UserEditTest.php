<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\UserEdit;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class UserEditTest extends TestCase
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(UserEdit::class)
            ->assertStatus(200);
    }
}
