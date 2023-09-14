<?php

namespace FluxErp\Tests\Livewire\Features;

use FluxErp\Livewire\Features\Activities;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class ActivitiesTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(Activities::class)
            ->assertStatus(200);
    }
}
