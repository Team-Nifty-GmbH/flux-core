<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Livewire\Forms\PaymentRunForm;
use FluxErp\Models\BankConnection;
use FluxErp\Models\PaymentRun;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class PaymentRunList extends BaseDataTable
{
    public array $accounts = [];

    public array $enabledCols = [
        'bank_connection.iban',
        'state',
        'payment_run_type_enum',
    ];

    public ?string $includeBefore = 'flux::livewire.accounting.payment-run.include-before';

    public PaymentRunForm $paymentRunForm;

    protected string $model = PaymentRun::class;

    public function mount(): void
    {
        parent::mount();

        $this->accounts = resolve_static(BankConnection::class, 'query')
            ->get(['id', 'name', 'iban'])
            ->toArray();
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->color('indigo')
                ->wireClick(<<<'JS'
                    edit(record.id);
                JS),
        ];
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

    public function edit(PaymentRun $paymentRun): void
    {
        $this->loadPaymentRun($paymentRun);

        $this->js(<<<'JS'
            $modalOpen('execute-payment-run');
        JS);
    }

    public function executePaymentRun(): bool
    {
        // TODO: Create SEPA File in xml format

        return true;
    }

    public function removeOrder(int $id): bool
    {
        $paymentRun = resolve_static(PaymentRun::class, 'query')
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
                        'orders.iban',
                    ])
                    ->with(['contactBankConnection:id,iban', 'addressInvoice:id,name'])
                    ->withPivot('amount'),
            ])
            ->loadSum('orders AS total_amount', 'order_payment_run.amount');

        $this->paymentRunForm->fill($paymentRun);
    }
}
