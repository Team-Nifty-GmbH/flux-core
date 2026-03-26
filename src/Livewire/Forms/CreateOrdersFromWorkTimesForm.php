<?php

namespace FluxErp\Livewire\Forms;

class CreateOrdersFromWorkTimesForm extends FluxForm
{
    public bool $add_non_billable_work_times = true;

    public ?int $order_type_id = null;

    public ?int $product_id = null;

    public ?int $tenant_id = null;

    public string $round = 'ceil';

    public ?int $round_to_minute = null;

    protected function getActions(): array
    {
        return [];
    }
}
