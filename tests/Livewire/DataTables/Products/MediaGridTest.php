<?php

namespace Tests\Feature\Livewire\DataTables\Products;

use FluxErp\Livewire\DataTables\Products\MediaGrid;
use Livewire\Livewire;
use Tests\TestCase;

class MediaGridTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(MediaGrid::class)
            ->assertStatus(200);
    }
}
