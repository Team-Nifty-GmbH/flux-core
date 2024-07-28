<?php

namespace Tests\Feature\Livewire\Contact;

use FluxErp\Livewire\Contact\Projects;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectsTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Projects::class)
            ->assertStatus(200);
    }
}
