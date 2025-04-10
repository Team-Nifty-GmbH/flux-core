<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\AttributeTranslationList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class AttributeTranslationListTest extends TestCase
{
    protected string $livewireComponent = AttributeTranslationList::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
