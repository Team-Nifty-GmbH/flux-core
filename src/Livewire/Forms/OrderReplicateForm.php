<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Order\ReplicateOrder;
use Livewire\Attributes\Locked;

class OrderReplicateForm extends FluxForm
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

    public ?array $order_positions = null;

    protected function getActions(): array
    {
        return [
            'update' => ReplicateOrder::class,
        ];
    }
}
