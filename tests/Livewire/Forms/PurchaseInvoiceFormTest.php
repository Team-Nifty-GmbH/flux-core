<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\PurchaseInvoiceForm;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseInvoiceFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(PurchaseInvoiceForm::class)
            ->assertStatus(200);
    }
}
