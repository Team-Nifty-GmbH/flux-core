<?php

namespace FluxErp\Tests\Livewire\Contact;

use FluxErp\Livewire\Contact\Projects;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProjectsTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(Projects::class)
            ->assertStatus(200);
    }
}
