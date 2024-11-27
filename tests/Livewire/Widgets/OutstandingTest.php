<?php

namespace Tests\Feature\Livewire\Widgets;

use FluxErp\Livewire\Widgets\Outstanding;
use FluxErp\Models\Currency;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class OutstandingTest extends TestCase
{
    protected string $livewireComponent = Outstanding::class;

    protected function setUp(): void
    {
        parent::setUp();

        Currency::factory()->create(['is_default' => true]);
    }

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
