<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\Loan\DeleteLoan;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\LoanForm;
use FluxErp\Livewire\Forms\MediaUploadForm;
use FluxErp\Models\Currency;
use FluxErp\Models\Loan as LoanModel;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithFileUploads;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Loan extends Component
{
    use Actions, WithFileUploads, WithTabs;

    public MediaUploadForm $contract;

    public array $installments = [];

    public LoanForm $loan;

    public array $queryString = [
        'tab' => ['except' => 'loan.general'],
    ];

    public string $tab = 'loan.general';

    public array $totals = [];

    protected ?string $currencyIso = null;

    public function mount(string $id): void
    {
        $loan = resolve_static(LoanModel::class, 'query')
            ->whereKey($id)
            ->firstOrFail();

        try {
            $this->getTabButton($this->tab);
        } catch (Throwable) {
            throw new NotFoundHttpException('Tab not found');
        }

        $this->loan->fill($loan);
        $this->contract->fill($loan->getFirstMedia('contract') ?? []);
        $this->installments = $this->buildSchedule($loan);
        $this->totals = $this->buildTotals($loan);
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.accounting.loan');
    }

    public function delete(): void
    {
        $this->skipRender();

        try {
            DeleteLoan::make(['id' => $this->loan->id])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->redirect(route('accounting.loans'));
    }

    #[Computed]
    public function scheduleHeaders(): array
    {
        return [
            ['index' => 'sequence', 'label' => __('Sequence')],
            ['index' => 'due_date', 'label' => __('Due Date')],
            ['index' => 'principal_amount', 'label' => __('Principal')],
            ['index' => 'interest_amount', 'label' => __('Interest')],
            ['index' => 'remaining', 'label' => __('Remaining')],
            ['index' => 'is_paid', 'label' => __('Paid')],
        ];
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('loan.general')
                ->text(__('General')),
            TabButton::make('loan.installments')
                ->text(__('Repayment Schedule')),
            TabButton::make('loan.documents')
                ->text(__('Documents')),
        ];
    }

    public function resetForm(): void
    {
        $loan = resolve_static(LoanModel::class, 'query')
            ->whereKey($this->loan->id)
            ->firstOrFail();

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

        $this->contract->model_type = morph_alias(LoanModel::class);
        $this->contract->model_id = $this->loan->id;
        $this->contract->collection_name = 'contract';

        if ($this->contract->stagedFiles || $this->contract->id) {
            try {
                $this->contract->save();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
            }
        }

        $this->toast()
            ->success(__(':model saved', ['model' => __('Loan')]))
            ->send();

        return true;
    }

    /**
     * The stored installments plus a running remaining balance, formatted for
     * display because the table renders the values as they are.
     */
    protected function buildSchedule(LoanModel $loan): array
    {
        $remaining = $loan->amount;
        $schedule = [];

        foreach ($loan->installments()->orderBy('sequence')->get() as $installment) {
            $remaining = bcsub($remaining, $installment->principal_amount, 2);

            $schedule[] = [
                'sequence' => $installment->sequence,
                'due_date' => $installment->due_date->locale(app()->getLocale())->isoFormat('L'),
                'principal_amount' => $this->money($installment->principal_amount),
                'interest_amount' => $this->money($installment->interest_amount),
                'is_paid' => $installment->is_paid,
                'remaining' => $this->money($remaining),
            ];
        }

        return $schedule;
    }

    /**
     * The sums above the schedule: what has to be repaid over the whole term
     * and how much of it is settled already.
     */
    protected function buildTotals(LoanModel $loan): array
    {
        $principal = (string) $loan->installments()->sum('principal_amount');
        $interest = (string) $loan->installments()->sum('interest_amount');
        $paidPrincipal = (string) $loan->installments()->where('is_paid', true)->sum('principal_amount');
        $paidInterest = (string) $loan->installments()->where('is_paid', true)->sum('interest_amount');

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

    protected function share(string $part, string $of): string
    {
        return Number::percentage(
            bccomp($of, '0', 2) === 1 ? (float) bcmul(bcdiv($part, $of, 6), '100', 4) : 0,
            1,
            locale: app()->getLocale()
        );
    }

    protected function currencyIso(): string
    {
        return $this->currencyIso ??= resolve_static(Currency::class, 'query')
            ->where('is_default', true)
            ->value('iso') ?? 'EUR';
    }

    protected function money(string|float|int|null $value): string
    {
        return Number::currency((float) $value, $this->currencyIso(), app()->getLocale());
    }
}
