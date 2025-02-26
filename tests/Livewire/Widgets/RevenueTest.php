<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\Revenue;
use FluxErp\Models\Currency;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class RevenueTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Currency::factory()->create([
            'is_default' => true,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(Revenue::class)
            ->assertStatus(200);
    }
}
