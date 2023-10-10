<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\CustomerPortal;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class CustomerPortalTest extends TestCase
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(CustomerPortal::class)
            ->assertStatus(200);
    }
}
