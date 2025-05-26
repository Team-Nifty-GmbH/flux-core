<?php

namespace FluxErp\Tests\Livewire\Contact;

use FluxErp\Livewire\Contact\Leads;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class LeadsTest extends TestCase
{
    protected string $livewireComponent = Leads::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
