<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\RecentActivities;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class RecentActivitiesTest extends TestCase
{
    protected string $livewireComponent = RecentActivities::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
