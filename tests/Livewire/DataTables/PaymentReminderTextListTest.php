<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\PaymentReminderTextList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class PaymentReminderTextListTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(PaymentReminderTextList::class)
            ->assertStatus(200);
    }
}
