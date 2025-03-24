<?php

namespace FluxErp\Tests\Livewire\Product;

use FluxErp\Livewire\Product\Attachments;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class AttachmentsTest extends TestCase
{
    protected string $livewireComponent = Attachments::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
