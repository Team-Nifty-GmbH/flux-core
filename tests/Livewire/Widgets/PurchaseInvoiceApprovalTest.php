<?php

namespace FluxErp\Tests\Livewire\DataTablesWidgets;

use FluxErp\Livewire\Widgets\PurchaseInvoiceApproval;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class PurchaseInvoiceApprovalTest extends TestCase
{
    protected string $livewireComponent = PurchaseInvoiceApproval::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
