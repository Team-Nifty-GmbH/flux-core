<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\PurchaseInvoiceList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class PurchaseInvoiceListTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(PurchaseInvoiceList::class)
            ->assertStatus(200);
    }
}
