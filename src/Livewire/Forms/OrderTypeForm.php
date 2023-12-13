<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\OrderType\CreateOrderType;
use FluxErp\Actions\OrderType\UpdateOrderType;
use Livewire\Attributes\Locked;
use Livewire\Form;

class OrderTypeForm extends Form
{
    #[Locked]
    public ?int $id = null;

    public ?int $client_id = null;

    public ?string $name = null;

    public ?string $description = null;

    public ?string $mail_subject = null;

    public ?string $mail_body = null;

    public ?array $print_layouts = [];

    public ?string $order_type_enum = null;

    public bool $is_active = true;

    public bool $is_hidden = false;

    public function save(): void
    {

        $action = $this->id ? UpdateOrderType::make($this->toArray()) : CreateOrderType::make($this->toArray());

        $response = $action->checkPermission()->validate()->execute();

        $this->fill($response);
    }

    public function fill($values)
    {
        parent::fill($values);

        $this->print_layouts = $this->print_layouts ?? [];
    }
}
