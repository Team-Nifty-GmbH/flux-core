<?php

namespace FluxErp\Tests\Livewire\Contact\Accounting;

use FluxErp\Livewire\Contact\Accounting\AllDiscounts;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class AllDiscountsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(AllDiscounts::class)
            ->assertStatus(200);
    }
}
