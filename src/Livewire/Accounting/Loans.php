<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\Loan\CreateLoan;
use FluxErp\Actions\Loan\DeleteLoan;
use FluxErp\Actions\Loan\UpdateLoan;
use FluxErp\Livewire\DataTables\LoanList;
use FluxErp\Livewire\Forms\LoanForm;
use FluxErp\Models\Loan;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Loans extends LoanList
{
    public ?string $includeBefore = 'flux::livewire.accounting.loans';

    public LoanForm $loan;

    public array $installments = [];

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->when(resolve_static(CreateLoan::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit()',
                ]),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->when(resolve_static(UpdateLoan::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->when(resolve_static(DeleteLoan::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Loan')]),
                    'wire:click' => 'delete(record.id)',
                ]),
        ];
    }

    public function delete(Loan $loan): bool
    {
        $this->loan->reset();
        $this->loan->fill($loan);

        try {
            $this->loan->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function edit(?Loan $loan = null): void
    {
        $this->loan->reset();
        $this->installments = [];

        if ($loan?->exists) {
            $this->loan->fill($loan);
            $this->installments = $this->buildSchedule($loan);
        }

        $this->modalOpen('edit-loan-modal');
    }

    public function save(): bool
    {
        try {
            $this->loan->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    /**
     * The stored installments plus a running remaining balance for display.
     */
    protected function buildSchedule(Loan $loan): array
    {
        $remaining = $loan->amount;
        $schedule = [];

        foreach ($loan->installments()->orderBy('sequence')->get() as $installment) {
            $remaining = bcsub($remaining, $installment->principal_amount, 2);

            $schedule[] = [
                'sequence' => $installment->sequence,
                'due_date' => $installment->due_date->toDateString(),
                'principal_amount' => $installment->principal_amount,
                'interest_amount' => $installment->interest_amount,
                'is_paid' => $installment->is_paid,
                'remaining' => $remaining,
            ];
        }

        return $schedule;
    }
}
