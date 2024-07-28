<?php

namespace Tests\Feature\Livewire\Settings;

use FluxErp\Livewire\Settings\WorkTimeTypes;
use Livewire\Livewire;
use Tests\TestCase;

class WorkTimeTypesTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(WorkTimeTypes::class)
            ->assertStatus(200);
    }
}
