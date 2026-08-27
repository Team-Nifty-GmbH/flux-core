<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\Transaction\DeleteTransaction;
use FluxErp\Livewire\DataTables\TransactionList as BaseTransactionList;
use FluxErp\Support\Bus\BulkExecutor;
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
        $transactionIds = $this->getSelectedModelsQuery()->pluck('id');

        if ($transactionIds->isEmpty()) {
            return;
        }

        try {
            BulkExecutor::make(
                DeleteTransaction::class,
                $transactionIds
                    ->map(fn (int $id): array => ['id' => $id])
                    ->all()
            )
                ->name(__('Deleting transactions'))
                ->dispatch();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->reset('selected');
    }
}
