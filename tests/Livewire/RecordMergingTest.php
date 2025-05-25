<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\RecordMerging;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class RecordMergingTest extends TestCase
{
    protected string $livewireComponent = RecordMerging::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
