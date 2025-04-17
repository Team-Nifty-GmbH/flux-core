<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\OrderTransaction\CreateOrderTransaction;
use FluxErp\Actions\OrderTransaction\DeleteOrderTransaction;
use FluxErp\Actions\OrderTransaction\UpdateOrderTransaction;
use Livewire\Attributes\Locked;

class OrderTransactionForm extends FluxForm
{
    public ?float $amount = null;

    public bool $is_accepted = false;

    #[Locked]
    public ?int $order_id = null;

    #[Locked]
    public ?int $pivot_id = null;

    #[Locked]
    public ?int $transaction_id = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateOrderTransaction::class,
            'update' => UpdateOrderTransaction::class,
            'delete' => DeleteOrderTransaction::class,
        ];
    }

    protected function getKey(): string
    {
        return 'pivot_id';
    }
}
