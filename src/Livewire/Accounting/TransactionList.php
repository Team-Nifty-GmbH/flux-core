<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\Transaction\DeleteTransaction;
use FluxErp\Livewire\DataTables\TransactionList as BaseTransactionList;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class TransactionList extends BaseTransactionList
{
    public bool $isSelectable = true;

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->when(fn () => resolve_static(DeleteTransaction::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Transaction')]),
                    'wire:click' => 'deleteSelected()',
                ]),
        ];
    }

    #[Renderless]
    public function deleteSelected(): void
    {
        try {
            $this->getSelectedModelsQuery()->pluck('id')->each(function (int $id): void {
                DeleteTransaction::make(['id' => $id])
                    ->checkPermission()
                    ->validate()
                    ->execute();
            });
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }

        $this->loadData();

        $this->reset('selected');
    }
}
