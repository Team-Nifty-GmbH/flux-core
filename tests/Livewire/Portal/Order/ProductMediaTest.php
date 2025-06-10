<?php

namespace FluxErp\Tests\Livewire\Portal\Order;

use FluxErp\Livewire\Portal\Order\ProductMedia;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProductMediaTest extends TestCase
{
    protected string $livewireComponent = ProductMedia::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
