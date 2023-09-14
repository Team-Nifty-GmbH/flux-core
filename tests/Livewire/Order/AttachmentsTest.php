<?php

namespace FluxErp\Tests\Livewire\Order;

use FluxErp\Livewire\Order\Attachments;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class AttachmentsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(Attachments::class, ['orderId' => 1])
            ->assertStatus(200);
    }
}
