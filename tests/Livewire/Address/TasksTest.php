<?php

namespace FluxErp\Tests\Livewire\Address;

use FluxErp\Livewire\Address\Tasks;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class TasksTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(Tasks::class, ['modelId' => $this->address->id])
            ->assertStatus(200);
    }
}
