<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\DiscountGroups;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class DiscountGroupsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(DiscountGroups::class)
            ->assertStatus(200);
    }
}
