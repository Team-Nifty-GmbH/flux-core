<?php

namespace FluxErp\Tests\Livewire\Contact\Accounting;

use FluxErp\Livewire\Contact\Accounting\General;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class GeneralTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(General::class)
            ->assertStatus(200);
    }
}
