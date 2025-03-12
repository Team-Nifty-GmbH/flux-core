<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\FailedJobs;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class FailedJobsTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(FailedJobs::class)
            ->assertStatus(200);
    }
}
