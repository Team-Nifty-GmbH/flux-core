<?php

namespace FluxErp\Tests\Livewire\Contact\Accounting;

use FluxErp\Livewire\Contact\Accounting\Discounts;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class DiscountsTest extends TestCase
{
    protected string $livewireComponent = Discounts::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
