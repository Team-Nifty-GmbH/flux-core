<?php

namespace FluxErp\Tests\Livewire\Features;

use FluxErp\Livewire\Features\Notifications;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class NotificationsTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Notifications::class)
            ->assertStatus(200);
    }
}
