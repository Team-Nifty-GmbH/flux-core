<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\Order\DeleteOrder;
use FluxErp\Actions\Order\UpdateOrder;

class OrderForm extends FluxForm
{
    public ?int $id = null;

    public ?int $client_id = null;

    public ?int $agent_id = null;

    public ?int $contact_id = null;

    public ?int $address_invoice_id = null;

    public ?int $address_delivery_id = null;

    public ?int $language_id = null;

    public ?int $order_type_id = null;

    public ?int $price_list_id = null;

    public ?int $payment_type_id = null;

    public int $payment_target = 1;

    public ?int $payment_discount_target = null;

    public ?int $payment_reminder_days_1 = null;

    public ?int $payment_reminder_days_2 = null;

    public ?int $payment_reminder_days_3 = null;

    public ?int $payment_discount_percent = null;

    public ?array $payment_texts = [];

    protected function getActions(): array
    {
        return [
            'create' => CreateOrder::class,
            'update' => UpdateOrder::class,
            'delete' => DeleteOrder::class,
        ];
    }
}
