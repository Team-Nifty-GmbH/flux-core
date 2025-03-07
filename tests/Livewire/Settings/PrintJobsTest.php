<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\PrintJobs;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class PrintJobsTest extends TestCase
{
    protected string $livewireComponent = PrintJobs::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
