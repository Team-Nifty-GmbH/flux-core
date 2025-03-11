<?php

namespace FluxErp\Tests\Livewire\Project;

use FluxErp\Livewire\Project\ProjectList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class ProjectListTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(ProjectList::class)
            ->assertStatus(200);
    }
}
