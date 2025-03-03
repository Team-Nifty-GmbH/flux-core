<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\DiscountGroups;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class DiscountGroupsTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(DiscountGroups::class)
            ->assertStatus(200);
    }
}
