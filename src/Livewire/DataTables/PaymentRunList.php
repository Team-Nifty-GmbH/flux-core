<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Order\UpdateOrder;
use FluxErp\Livewire\Forms\PaymentRunForm;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentRun;
use FluxErp\States\Order\PaymentState\Open;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
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

    #[Renderless]
    public function edit(PaymentRun $paymentRun): void
    {
        $this->loadPaymentRun($paymentRun);

        $this->js(<<<'JS'
            $tsui.open.modal('execute-payment-run');
        JS);
    }

    public function executePaymentRun(): bool
    {
        try {
            $this->paymentRunForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function removeOrder(int $id): bool
    {
        $paymentRunId = $this->paymentRunForm->id;

        $paymentRun = resolve_static(PaymentRun::class, 'query')
            ->whereKey($paymentRunId)
            ->first();
        $order = resolve_static(Order::class, 'query')
            ->select(['id', 'payment_state'])
            ->whereKey($id)
            ->first();

        $paymentRun->orders()->detach($id);

        if ($order?->payment_state->canTransitionTo(Open::class)) {
            try {
                UpdateOrder::make([
                    'id' => $order->getKey(),
                    'payment_state' => Open::$name,
                ])
                    ->validate()
                    ->execute();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
            }
        }

        $paymentRun = resolve_static(PaymentRun::class, 'query')
            ->whereKey($paymentRunId)
            ->first();

        if ($paymentRun->orders()->doesntExist()) {
            $this->delete();

            return true;
        }

        $this->loadPaymentRun($paymentRun);

        return false;
    }

    protected function loadPaymentRun(PaymentRun $paymentRun): void
    {
        $paymentRun
            ->load([
                'orders' => fn (BelongsToMany $query) => $query
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
        $this->paymentRunForm->orders = $paymentRun->orders->toArray();
    }
}
