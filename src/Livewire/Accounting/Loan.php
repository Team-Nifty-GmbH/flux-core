<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\Loan\DeleteLoan;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\FinanceOrderForm;
use FluxErp\Livewire\Forms\LoanForm;
use FluxErp\Livewire\Forms\MediaUploadForm;
use FluxErp\Models\Currency;
use FluxErp\Models\Loan as LoanModel;
use FluxErp\Models\LoanInstallment;
use FluxErp\Models\Order as OrderModel;
use FluxErp\Models\Transaction as TransactionModel;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithFileUploads;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Loan extends Component
{
    use Actions, WithFileUploads, WithTabs;

    public MediaUploadForm $contract;

    public FinanceOrderForm $financeOrder;

    #[Locked]
    public string $currencyIso = 'EUR';

    public LoanForm $loan;

    public array $queryString = [
        'tab' => ['except' => 'loan.general'],
    ];

    public string $tab = 'loan.general';

    #[Locked]
    public array $totals = [];

    protected ?Collection $loadedInstallments = null;

    public function mount(string $id): void
    {
        try {
            $this->getTabButton($this->tab);
        } catch (Throwable) {
            throw new NotFoundHttpException('Tab not found');
        }

        $this->currencyIso = resolve_static(Currency::class, 'query')
            ->where('is_default', true)
            ->value('iso') ?? 'EUR';

        $loan = $this->loadLoan($id);

        $this->loan->fill($loan);
        $this->resetFinanceOrderForm($loan);
        $this->contract->fill($loan->getFirstMedia('contract') ?? []);
        $this->totals = $this->buildTotals($loan);
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.accounting.loan');
    }

    #[Renderless]
    public function delete(): void
    {
        try {
            DeleteLoan::make(['id' => $this->loan->id])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this, form: $this->loan);

            return;
        }

        $this->redirect(route('accounting.loans'), navigate: true);
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('loan.general')
                ->text(__('General')),
            TabButton::make('loan.installments')
                ->text(__('Repayment Schedule')),
            TabButton::make('loan.payments')
                ->text(__('Payments')),
            TabButton::make('loan.bookings')
                ->text(__('Bookings')),
            TabButton::make('loan.documents')
                ->text(__('Documents')),
        ];
    }

    #[Renderless]
    public function changedFinancedOrder(int $orderId): void
    {
        $order = resolve_static(OrderModel::class, 'query')
            ->with(['contact:id,expense_ledger_account_id', 'orderType:id,order_type_enum'])
            ->whereKey($orderId)
            ->first(['id', 'tenant_id', 'contact_id', 'order_type_id', 'balance']);

        if (! $order) {
            return;
        }

        if ($order->tenant_id !== $this->loan->tenant_id
            || ! $order->orderType?->order_type_enum?->isPurchase()
        ) {
            $this->financeOrder->order_id = null;

            $this->notification()
                ->error(__('Only purchase orders can be financed.'))
                ->send();

            return;
        }

        $this->financeOrder->debit_ledger_account_id = $order->contact?->expense_ledger_account_id;
        $this->financeOrder->amount = (float) bcround(abs((float) $order->balance), 2);
    }

    public function finance(): bool
    {
        $this->resetErrorBag();

        try {
            $this->financeOrder->create();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this, form: $this->financeOrder);

            return false;
        }

        $loan = $this->loadLoan($this->loan->id);

        $this->loan->reset();
        $this->loan->fill($loan);

        $this->resetFinanceOrderForm($loan);

        $this->toast()
            ->success(__('Order financed'))
            ->send();

        return true;
    }

    #[Renderless]
    public function resetForm(): void
    {
        $loan = $this->loadLoan($this->loan->id);

        $this->loan->reset();
        $this->loan->fill($loan);
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->loan->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this, form: $this->loan);

            return false;
        }

        $this->toast()
            ->success(__(':model saved', ['model' => __('Loan')]))
            ->send();

        return true;
    }

    #[Renderless]
    public function saveContract(): bool
    {
        $this->contract->model_type = morph_alias(LoanModel::class);
        $this->contract->model_id = $this->loan->id;
        $this->contract->collection_name = 'contract';

        try {
            $this->contract->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this, form: $this->contract);

            return false;
        }

        $this->toast()
            ->success(__(':model saved', ['model' => __('Contract')]))
            ->send();

        return true;
    }

    protected function resetFinanceOrderForm(LoanModel $loan): void
    {
        $this->financeOrder->reset();
        $this->financeOrder->loan_id = $loan->getKey();
        $this->financeOrder->booking_date = now()->toDateString();
    }

    protected function coveredAmount(LoanInstallment $installment): string
    {
        $sum = $installment->transactions
            ->where('pivot.is_accepted', true)
            ->reduce(
                fn (string $carry, TransactionModel $transaction) => bcadd(
                    $carry,
                    (string) $transaction->pivot->amount,
                    2
                ),
                '0'
            );

        return bccomp($sum, '0', 2) === -1 ? bcabs($sum) : '0.00';
    }

    protected function loadInstallments(LoanModel $loan): Collection
    {
        return $this->loadedInstallments ??= $loan->installments()
            ->with('transactions')
            ->orderBy('sequence')
            ->get();
    }

    protected function buildTotals(LoanModel $loan): array
    {
        $principal = '0';
        $interest = '0';
        $paidPrincipal = '0';
        $paidInterest = '0';

        foreach ($this->loadInstallments($loan) as $installment) {
            $principal = bcadd($principal, (string) $installment->principal_amount, 2);
            $interest = bcadd($interest, (string) $installment->interest_amount, 2);

            if ($installment->is_paid
                || bccomp($this->coveredAmount($installment), $installment->getTotalAmount(), 2) !== -1
            ) {
                $paidPrincipal = bcadd($paidPrincipal, (string) $installment->principal_amount, 2);
                $paidInterest = bcadd($paidInterest, (string) $installment->interest_amount, 2);
            }
        }

        return [
            'principal_amount' => $this->money($principal),
            'interest_amount' => $this->money($interest),
            'total' => $this->money(bcadd($principal, $interest, 2)),
            'paid_principal_amount' => $this->money($paidPrincipal),
            'paid_interest_amount' => $this->money($paidInterest),
            'paid_total' => $this->money(bcadd($paidPrincipal, $paidInterest, 2)),
            'paid_principal_share' => $this->share($paidPrincipal, $principal),
            'paid_interest_share' => $this->share($paidInterest, $interest),
            'paid_total_share' => $this->share(
                bcadd($paidPrincipal, $paidInterest, 2),
                bcadd($principal, $interest, 2)
            ),
        ];
    }

    protected function loadLoan(int|string $id): LoanModel
    {
        return resolve_static(LoanModel::class, 'query')
            ->whereKey($id)
            ->firstOrFail();
    }

    protected function money(string|float|int|null $value): string
    {
        return Number::currency((float) $value, $this->currencyIso, app()->getLocale());
    }

    protected function share(string $part, string $of): string
    {
        return Number::percentage(
            bccomp($of, '0', 2) === 1 ? (float) bcmul(bcdiv($part, $of, 6), '100', 4) : 0,
            1,
            locale: app()->getLocale()
        );
    }
}
