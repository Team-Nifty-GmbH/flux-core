<?php

namespace Tests\Feature\Livewire\Contact\Accounting;

use FluxErp\Livewire\Contact\Accounting\SepaMandates;
use Livewire\Livewire;
use Tests\TestCase;

class SepaMandatesTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(SepaMandates::class)
            ->assertStatus(200);
    }
}
