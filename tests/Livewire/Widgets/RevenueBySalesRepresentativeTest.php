<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\RevenueBySalesRepresentative;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class RevenueBySalesRepresentativeTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(RevenueBySalesRepresentative::class)
            ->assertStatus(200);
    }
}
