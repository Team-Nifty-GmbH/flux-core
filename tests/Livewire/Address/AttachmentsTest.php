<?php

namespace FluxErp\Tests\Livewire\Address;

use FluxErp\Livewire\Address\Attachments;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class AttachmentsTest extends TestCase
{
    protected string $livewireComponent = Attachments::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
