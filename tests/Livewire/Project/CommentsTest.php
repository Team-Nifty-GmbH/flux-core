<?php

namespace FluxErp\Tests\Livewire\Project;

use FluxErp\Livewire\Project\Comments;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CommentsTest extends TestCase
{
    protected string $livewireComponent = Comments::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
