<?php

namespace FluxErp\Tests\Livewire\SignaturePublicLink;

use FluxErp\Livewire\SignaturePublicLink\Comments;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CommentsTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::withoutLazyLoading()
            ->test(Comments::class)
            ->assertStatus(200);
    }
}
