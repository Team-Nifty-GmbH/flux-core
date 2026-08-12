<?php

namespace FluxErp\Livewire\Accounting;

use Carbon\Carbon;
use FluxErp\Actions\Loan\DeleteLoan;
use FluxErp\Enums\ScheduleAdjustmentTypeEnum;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\FinanceOrderForm;
use FluxErp\Livewire\Forms\LoanExtraRepaymentForm;
use FluxErp\Livewire\Forms\LoanForm;
use FluxErp\Livewire\Forms\MediaUploadForm;
use FluxErp\Models\Currency;
use FluxErp\Models\Loan as LoanModel;
use FluxErp\Models\LoanExtraRepayment as LoanExtraRepaymentModel;
use FluxErp\Models\LoanInstallment;
use FluxErp\Models\Order as OrderModel;
use FluxErp\Models\Transaction as TransactionModel;
use FluxErp\Support\Calculation\ExtraRepaymentScheduler;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithFileUploads;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
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

    #[Locked]
    public array $allowance = [];

    public LoanExtraRepaymentForm $extraRepayment;

    #[Locked]
    public array $extraRepayments = [];

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
        $this->extraRepayments = $this->buildExtraRepayments($loan);
        $this->allowance = $this->buildAllowance($loan);

        $this->extraRepayment->loan_id = $loan->getKey();
        $this->extraRepayment->executed_at = today()->toDateString();
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
            exception_to_notifications($e, $this);

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
            TabButton::make('loan.extra-repayments')
                ->text(__('Extra Repayments')),
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
            exception_to_notifications($e, $this);

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
    public function saveExtraRepayment(): bool
    {
        try {
            $this->extraRepayment->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->toast()
            ->success(__(':model saved', ['model' => __('Extra Repayment')]))
            ->send();

        $this->reloadLoan();

        return true;
    }

    #[Computed]
    public function extraRepaymentHeaders(): array
    {
        return [
            ['index' => 'executed_at', 'label' => __('Executed At')],
            ['index' => 'amount', 'label' => __('Amount')],
            ['index' => 'schedule_adjustment_type', 'label' => __('Schedule Adjustment')],
            ['index' => 'interest_saved', 'label' => __('Interest Saved')],
            ['index' => 'installments_saved', 'label' => __('Installments Saved')],
            ['index' => 'note', 'label' => __('Note')],
        ];
    }

    #[Computed]
    public function extraRepaymentPreview(): array
    {
        $amount = (float) $this->extraRepayment->amount;

        if ($amount <= 0) {
            return [];
        }

        $loan = $this->loadLoan($this->loan->id);
        $scheduler = app(ExtraRepaymentScheduler::class);
        $open = $scheduler->openInstallments($loan);

        if ($open->isEmpty() || bccomp((string) $amount, (string) $loan->remaining, 2) === 1) {
            return [];
        }

        $schedule = $scheduler->reschedule(
            $loan,
            $amount,
            ScheduleAdjustmentTypeEnum::from($this->extraRepayment->schedule_adjustment_type_enum),
            $open
        );
        $savings = $scheduler->savings($schedule, $open);
        $lastInstallment = array_last($schedule);

        return [
            'interest_saved' => $this->money($savings['interest_saved']),
            'installments_saved' => $savings['installments_saved'],
            'remaining' => $this->money(bcsub((string) $loan->remaining, (string) $amount, 2)),
            'ends_at' => $lastInstallment
                ? Carbon::parse($lastInstallment['due_date'])
                    ->locale(app()->getLocale())
                    ->isoFormat('L')
                : __('Settled'),
        ];
    }

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
            exception_to_notifications($e, $this);

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
            exception_to_notifications($e, $this);

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

    protected function buildAllowance(LoanModel $loan): array
    {
        $year = today()->year;
        $allowance = $loan->extraRepaymentAllowance();

        return [
            'is_allowed' => (bool) $loan->allows_extra_repayments,
            'is_capped' => ! is_null($allowance),
            'year' => $year,
            'allowance' => is_null($allowance) ? null : $this->money($allowance),
            'used' => $this->money($loan->usedExtraRepayments($year)),
            'remaining' => is_null($allowance)
                ? null
                : $this->money($loan->remainingExtraRepaymentAllowance($year)),
        ];
    }

    protected function buildExtraRepayments(LoanModel $loan): array
    {
        return $loan->extraRepayments()
            ->orderByDesc('executed_at')
            ->get()
            ->map(fn (LoanExtraRepaymentModel $extraRepayment): array => [
                'id' => $extraRepayment->getKey(),
                'executed_at' => $extraRepayment->executed_at
                    ?->locale(app()->getLocale())
                    ->isoFormat('L'),
                'amount' => $this->money($extraRepayment->amount),
                'schedule_adjustment_type' => __(
                    Str::headline($extraRepayment->schedule_adjustment_type_enum->value)
                ),
                'interest_saved' => $this->money($extraRepayment->interest_saved),
                'installments_saved' => $extraRepayment->installments_saved,
                'note' => $extraRepayment->note,
            ])
            ->toArray();
    }

    protected function reloadLoan(): void
    {
        $this->loadedInstallments = null;

        $loan = $this->loadLoan($this->loan->id);

        $this->loan->fill($loan);
        $this->totals = $this->buildTotals($loan);
        $this->extraRepayments = $this->buildExtraRepayments($loan);
        $this->allowance = $this->buildAllowance($loan);

        $this->extraRepayment->reset('amount', 'note');
        $this->extraRepayment->loan_id = $loan->getKey();
        $this->extraRepayment->executed_at = today()->toDateString();

        unset($this->extraRepaymentPreview);
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
