<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\WorkTimeTypes;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class WorkTimeTypesTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(WorkTimeTypes::class)
            ->assertStatus(200);
    }
}
