<?php

namespace FluxErp\Tests\Livewire\Contact\Accounting;

use FluxErp\Livewire\Contact\Accounting\AllDiscounts;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class AllDiscountsTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(AllDiscounts::class)
            ->assertStatus(200);
    }
}
