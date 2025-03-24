<?php

namespace FluxErp\Tests\Livewire\Contact\Accounting;

use FluxErp\Livewire\Contact\Accounting\DiscountGroups;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class DiscountGroupsTest extends TestCase
{
    protected string $livewireComponent = DiscountGroups::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
