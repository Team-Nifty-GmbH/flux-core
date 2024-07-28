<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\UserForm;
use Livewire\Livewire;
use Tests\TestCase;

class UserFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(UserForm::class)
            ->assertStatus(200);
    }
}
