<?php

namespace FluxErp\Tests\Livewire\Accounting\Transactions;

use FluxErp\Livewire\Accounting\Transactions\Comments;
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
