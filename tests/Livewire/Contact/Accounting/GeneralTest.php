<?php

namespace Tests\Feature\Livewire\Contact\Accounting;

use FluxErp\Livewire\Contact\Accounting\General;
use Livewire\Livewire;
use Tests\TestCase;

class GeneralTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(General::class)
            ->assertStatus(200);
    }
}
