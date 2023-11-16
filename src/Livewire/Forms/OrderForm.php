<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\Order\UpdateOrder;
use Livewire\Form;

class OrderForm extends Form
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

    public int $payment_reminder_days_1 = 0;

    public int $payment_reminder_days_2 = 0;

    public int $payment_reminder_days_3 = 0;

    public ?int $payment_discount_percent = null;
    public ?array $payment_texts = [];

    public function save(): void
    {
        $action = $this->id
            ? UpdateOrder::make($this->toArray())
            : CreateOrder::make($this->toArray());

        $response = $action
            ->checkPermission()
            ->validate()
            ->execute();

        $this->fill($response);
    }
}
