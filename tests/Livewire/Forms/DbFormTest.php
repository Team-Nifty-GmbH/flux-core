<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\DbForm;
use Livewire\Livewire;
use Tests\TestCase;

class DbFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(DbForm::class)
            ->assertStatus(200);
    }
}
