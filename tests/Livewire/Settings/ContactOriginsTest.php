<?php

namespace Tests\Feature\Livewire\Settings;

use FluxErp\Livewire\Settings\ContactOrigins;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ContactOriginsTest extends TestCase
{
    protected string $livewireComponent = ContactOrigins::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
