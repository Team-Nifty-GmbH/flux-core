<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\MediaForm;
use Livewire\Livewire;
use Tests\TestCase;

class MediaFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(MediaForm::class)
            ->assertStatus(200);
    }
}
