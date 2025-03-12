<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\Product\MediaGrid;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class MediaGridTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(MediaGrid::class)
            ->assertStatus(200);
    }
}
