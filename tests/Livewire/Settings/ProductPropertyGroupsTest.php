<?php

namespace Tests\Feature\Livewire\Settings;

use FluxErp\Livewire\Settings\ProductPropertyGroups;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProductPropertyGroupsTest extends TestCase
{
    protected string $livewireComponent = ProductPropertyGroups::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
