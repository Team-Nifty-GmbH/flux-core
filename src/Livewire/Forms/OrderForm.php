<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\Order\DeleteOrder;
use FluxErp\Actions\Order\UpdateOrder;
use Livewire\Attributes\Locked;

class OrderForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $parent_id = null;

    public ?int $client_id = null;

    public ?int $agent_id = null;

    public ?int $contact_id = null;

    public ?int $address_invoice_id = null;

    public ?int $address_delivery_id = null;

    public ?int $language_id = null;

    public ?int $order_type_id = null;

    public ?int $price_list_id = null;

    public ?int $payment_type_id = null;

    public ?int $responsible_user_id = null;

    public ?array $address_invoice = null;

    public ?array $address_delivery = null;

    public ?string $state = null;

    public ?string $payment_state = null;

    public ?string $delivery_state = null;

    public int $payment_target = 1;

    public ?int $payment_discount_target = null;

    public ?int $payment_discount_percent = null;

    public ?string $total_net_price = null;

    public ?string $total_gross_price = null;

    public ?array $total_vats = null;

    public ?int $payment_reminder_days_1 = null;

    public ?int $payment_reminder_days_2 = null;

    public ?int $payment_reminder_days_3 = null;

    public ?string $order_number = null;

    public ?string $commission = null;

    public ?string $header = null;

    public ?string $footer = null;

    public ?string $logistic_note = null;

    public ?array $payment_texts = [];

    public ?string $order_date = null;

    public ?string $invoice_date = null;

    public ?string $invoice_number = null;

    public ?string $system_delivery_date = null;

    public bool $is_locked = false;

    public ?array $currency = null;

    public ?array $order_type = null;

    #[Locked]
    public ?string $created_at = null;

    #[Locked]
    public ?array $created_by = null;

    #[Locked]
    public ?string $updated_at = null;

    #[Locked]
    public ?array $updated_by = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateOrder::class,
            'update' => UpdateOrder::class,
            'delete' => DeleteOrder::class,
        ];
    }

    protected function makeAction(string $name, ?array $data = null): FluxAction
    {
        $data = $this->toArray();

        if (! $this->id) {
            unset(
                $data['state'],
                $data['payment_state'],
                $data['delivery_state'],
                $data['order_number'],
                $data['order_date'],
                $data['invoice_date'],
                $data['invoice_number']
            );
        }

        return parent::makeAction($name, $data);
    }
}
