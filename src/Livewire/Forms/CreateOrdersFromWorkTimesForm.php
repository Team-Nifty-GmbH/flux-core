<?php

namespace FluxErp\Livewire\Forms;

use Livewire\Attributes\Rule;
use Livewire\Form;

class CreateOrdersFromWorkTimesForm extends Form
{
    #[Rule('required|integer|exists:products,id,deleted_at,NULL')]
    public ?int $product_id = null;

    #[Rule('required|integer|exists:order_types,id,deleted_at,NULL')]
    public ?int $order_type_id = null;

    #[Rule('required_if:round,ceil|required_if:round,floor|nullable|integer')]
    public ?int $round_to_minute = null;

    #[Rule('boolean')]
    public bool $add_non_billable_work_times = true;

    #[Rule('in:floor,ceil,round')]
    public string $round = 'ceil';
}
