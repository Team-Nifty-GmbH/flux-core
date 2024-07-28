<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\LockedWorkTimeForm;
use Livewire\Livewire;
use Tests\TestCase;

class LockedWorkTimeFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(LockedWorkTimeForm::class)
            ->assertStatus(200);
    }
}
