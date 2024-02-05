<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Transaction\CreateTransaction;
use FluxErp\Actions\Transaction\DeleteTransaction;
use FluxErp\Actions\Transaction\UpdateTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Locked;

class TransactionForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $bank_connection_id = null;

    public ?int $currency_id = null;

    public ?int $parent_id = null;

    public ?int $order_id = null;

    public ?string $value_date = null;

    public ?string $booking_date = null;

    public ?float $amount = null;

    public ?string $purpose = null;

    public ?string $type = null;

    public ?string $counterpart_name = null;

    public ?string $counterpart_account_number = null;

    public ?string $counterpart_iban = null;

    public ?string $counterpart_bic = null;

    public ?string $counterpart_bank_name = null;

    public array $children = [];

    public ?float $difference = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateTransaction::class,
            'update' => UpdateTransaction::class,
            'delete' => DeleteTransaction::class,
        ];
    }

    public function fill($values): void
    {
        if ($values instanceof Model) {
            $values->loadMissing('children.order.contact.invoiceAddress:id,name');
        }

        parent::fill($values);

        $this->value_date = ! is_null(data_get($values, 'value_date')) ?
            Carbon::parse(data_get($values, 'value_date'))->toDateString() : null;
        $this->booking_date = ! is_null(data_get($values, 'booking_date')) ?
            Carbon::parse(data_get($values, 'booking_date'))->toDateString() : null;
    }
}
