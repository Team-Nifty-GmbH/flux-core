<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\OrderType\CreateOrderType;
use FluxErp\Actions\OrderType\DeleteOrderType;
use FluxErp\Actions\OrderType\UpdateOrderType;
use Livewire\Attributes\Locked;

class OrderTypeForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $client_id = null;

    public ?string $name = null;

    public ?string $description = null;

    public ?string $mail_subject = null;

    public ?string $mail_body = null;

    public ?array $print_layouts = [];

    public ?array $post_stock_print_layouts = [];

    public ?array $reserve_stock_print_layouts = [];

    public ?string $order_type_enum = null;

    public bool $is_active = true;

    public bool $is_hidden = false;

    public function fill($values): void
    {
        parent::fill($values);

        $this->print_layouts ??= [];
        $this->post_stock_print_layouts ??= [];
        $this->reserve_stock_print_layouts ??= [];
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateOrderType::class,
            'update' => UpdateOrderType::class,
            'delete' => DeleteOrderType::class,
        ];
    }
}
