<?php

namespace FluxErp\Tests\Livewire\Product\SerialNumber;

use FluxErp\Livewire\Product\SerialNumber\Comments;
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
