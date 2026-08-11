<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\Loan\CreateLoan;
use FluxErp\Actions\Loan\DeleteLoan;
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
    public function edit(): void
    {
        $this->loan->reset();

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

        $this->redirect(route('accounting.loans.id', ['id' => $this->loan->id]), navigate: true);

        return true;
    }
}
