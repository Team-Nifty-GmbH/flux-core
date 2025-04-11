<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\TokenList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TokenListTest extends TestCase
{
    protected string $livewireComponent = TokenList::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
