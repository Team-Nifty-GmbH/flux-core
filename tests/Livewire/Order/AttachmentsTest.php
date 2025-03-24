<?php

namespace FluxErp\Tests\Livewire\Order;

use FluxErp\Livewire\Order\Attachments;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class AttachmentsTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Attachments::class, ['orderId' => 1])
            ->assertStatus(200);
    }
}
