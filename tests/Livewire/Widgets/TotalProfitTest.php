<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\TotalProfit;
use FluxErp\Models\Currency;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class TotalProfitTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        Currency::factory()->create([
            'is_default' => true,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(TotalProfit::class)
            ->assertStatus(200);
    }
}
