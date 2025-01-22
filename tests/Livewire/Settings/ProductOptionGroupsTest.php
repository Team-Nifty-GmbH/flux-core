<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\ProductOptionGroups;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProductOptionGroupsTest extends TestCase
{
    protected string $livewireComponent = ProductOptionGroups::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
