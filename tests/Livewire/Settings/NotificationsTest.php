<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Notifications;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class NotificationsTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Notifications::class)
            ->assertStatus(200);
    }
}
