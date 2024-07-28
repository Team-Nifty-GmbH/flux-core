<?php

namespace Tests\Feature\Livewire\Widgets;

use FluxErp\Livewire\Widgets\RevenueBySalesRepresentative;
use Livewire\Livewire;
use Tests\TestCase;

class RevenueBySalesRepresentativeTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(RevenueBySalesRepresentative::class)
            ->assertStatus(200);
    }
}
