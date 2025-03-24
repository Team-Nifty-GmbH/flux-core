<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\RoleList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class RoleListTest extends TestCase
{
    protected string $livewireComponent = RoleList::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
