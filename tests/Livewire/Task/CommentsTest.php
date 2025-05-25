<?php

namespace FluxErp\Tests\Livewire\Task;

use FluxErp\Livewire\Task\Comments;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CommentsTest extends TestCase
{
    protected string $livewireComponent = Comments::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
