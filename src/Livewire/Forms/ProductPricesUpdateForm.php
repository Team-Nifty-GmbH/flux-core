<?php

namespace FluxErp\Livewire\Forms;

use Livewire\Form;

class ProductPricesUpdateForm extends Form
{
    public ?int $price_list_id = null;

    public ?int $base_price_list_id = null;

    public bool $is_percent = true;

    public ?float $alteration = null;

    public ?string $rounding_method_enum = 'none';

    public ?int $rounding_precision = null;

    public ?int $rounding_number = null;

    public ?string $rounding_mode = null;
}
