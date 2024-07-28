<?php

namespace Tests\Feature\Livewire\Settings;

use FluxErp\Livewire\Settings\SerialNumberRanges;
use Livewire\Livewire;
use Tests\TestCase;

class SerialNumberRangesTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(SerialNumberRanges::class)
            ->assertStatus(200);
    }
}
