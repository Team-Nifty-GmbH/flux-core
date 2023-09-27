<?php

namespace FluxErp\Tests\Livewire\Project;

use FluxErp\Livewire\Project\ProjectList;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class ProjectListTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(ProjectList::class)
            ->assertStatus(200);
    }
}
