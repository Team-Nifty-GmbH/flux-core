<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\MediaList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class MediaListTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(MediaList::class)
            ->assertStatus(200);
    }
}
