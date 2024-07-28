<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\PurchaseInvoiceList;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseInvoiceListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(PurchaseInvoiceList::class)
            ->assertStatus(200);
    }
}
