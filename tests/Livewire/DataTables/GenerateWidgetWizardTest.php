<?php

use FluxErp\Livewire\DataTables\GenerateWidgetWizard;
use FluxErp\Livewire\DataTables\OrderList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(GenerateWidgetWizard::class, ['datatable' => OrderList::class])
        ->assertOk();
});
