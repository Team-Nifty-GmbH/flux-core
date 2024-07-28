<?php

namespace FluxErp\Tests\Livewire\Contact\Accounting;

use FluxErp\Livewire\Contact\Accounting\SepaMandates;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class SepaMandatesTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(SepaMandates::class)
            ->assertStatus(200);
    }
}
