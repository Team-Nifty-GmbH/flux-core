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

    #[Locked]
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

    public ?string $difference = null;

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
        parent::fill($values);

        $this->value_date = ! is_null($valueDate = data_get($values, 'value_date')) ?
            Carbon::parse($valueDate)->toDateString() : null;
        $this->booking_date = ! is_null($bookingDate = data_get($values, 'booking_date')) ?
            Carbon::parse($bookingDate)->toDateString() : null;
    }
}
