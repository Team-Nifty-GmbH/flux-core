<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\LanguageLineList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class LanguageLineListTest extends TestCase
{
    protected string $livewireComponent = LanguageLineList::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
