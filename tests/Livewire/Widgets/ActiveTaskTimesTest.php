<?php

namespace Tests\Feature\Livewire\Widgets;

use FluxErp\Livewire\Widgets\ActiveTaskTimes;
use FluxErp\Models\WorkTime;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class ActiveTaskTimesTest extends BaseSetup
{
    protected string $livewireComponent = ActiveTaskTimes::class;

    protected function setUp(): void
    {
        parent::setUp();

        WorkTime::factory()
            ->for($this->user)
            ->create([
                'is_daily_work_time' => false,
                'is_locked' => false,
            ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200)
            ->assertCount('items', 1);
    }
}
