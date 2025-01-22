<?php

namespace FluxErp\Tests\Livewire\DataTablesSettings;

use FluxErp\Livewire\Settings\Tags;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TagsTest extends TestCase
{
    protected string $livewireComponent = Tags::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
