<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Livewire\Forms\PaymentRunForm;
use FluxErp\Models\BankConnection;
use FluxErp\Models\PaymentRun;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class PaymentRunList extends DataTable
{
    protected string $model = PaymentRun::class;

    public ?string $includeBefore = 'flux::livewire.accounting.payment-run.include-before';

    public array $enabledCols = [
        'bank_connection.iban',
        'state',
        'payment_run_type_enum',
    ];

    public array $accounts = [];

    public PaymentRunForm $paymentRunForm;

    public function mount(): void
    {
        parent::mount();

        $this->accounts = BankConnection::query()
            ->get(['id', 'name', 'iban'])
            ->toArray();
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->color('primary')
                ->wireClick(<<<'JS'
                    edit(record.id);
                JS),
        ];
    }

    public function executePaymentRun(): bool
    {
        // TODO: Create SEPA File in xml format

        return true;
    }

    public function edit(PaymentRun $paymentRun): void
    {
        $this->loadPaymentRun($paymentRun);

        $this->js(<<<'JS'
            $openModal('execute-payment-run');
        JS);
    }

    public function delete(): bool
    {
        try {
            $this->paymentRunForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function removeOrder(int $id): bool
    {
        $paymentRun = PaymentRun::query()
            ->whereKey($this->paymentRunForm->id)
            ->first();
        $paymentRun->orders()->detach($id);

        $this->loadPaymentRun($paymentRun);

        if (! $this->paymentRunForm->orders) {
            $this->delete();

            return true;
        }

        return false;
    }

    protected function loadPaymentRun(PaymentRun $paymentRun): void
    {
        $paymentRun
            ->loadMissing([
                'orders' => fn ($query) => $query
                    ->select([
                        'orders.id',
                        'orders.invoice_number',
                        'orders.contact_bank_connection_id',
                        'orders.address_invoice_id',
                    ])
                    ->with(['contactBankConnection:id,iban', 'addressInvoice:id,name'])
                    ->withPivot('amount'),
            ])
            ->loadSum('orders AS total_amount', 'order_payment_run.amount');

        $this->paymentRunForm->fill($paymentRun);
    }
}
