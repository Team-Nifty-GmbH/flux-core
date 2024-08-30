<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\AdditionalColumns;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class AdditionalColumnsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(AdditionalColumns::class)
            ->assertStatus(200);
    }
}
