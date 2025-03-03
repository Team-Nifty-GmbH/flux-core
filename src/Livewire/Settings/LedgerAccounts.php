<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\LedgerAccount\CreateLedgerAccount;
use FluxErp\Actions\LedgerAccount\DeleteLedgerAccount;
use FluxErp\Actions\LedgerAccount\UpdateLedgerAccount;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Livewire\DataTables\LedgerAccountList;
use FluxErp\Livewire\Forms\LedgerAccountForm;
use FluxErp\Models\LedgerAccount;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class LedgerAccounts extends LedgerAccountList
{
    use Actions;

    protected ?string $includeBefore = 'flux::livewire.settings.ledger-accounts';

    public LedgerAccountForm $ledgerAccount;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->when(resolve_static(CreateLedgerAccount::class, 'canPerformAction', [false]))
                ->wireClick('edit'),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->when(resolve_static(UpdateLedgerAccount::class, 'canPerformAction', [false]))
                ->wireClick('edit(record.id)'),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->icon('trash')
                ->when(resolve_static(DeleteLedgerAccount::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Ledger Account')]),
                ]),
        ];
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'ledgerAccountTypes' => LedgerAccountTypeEnum::values(),
            ]
        );
    }

    public function edit(LedgerAccount $ledgerAccount): void
    {
        $this->ledgerAccount->reset();
        $this->ledgerAccount->fill($ledgerAccount);

        $this->js(<<<'JS'
            $modalOpen('edit-ledger-account');
        JS);
    }

    public function save(): bool
    {
        try {
            $this->ledgerAccount->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function delete(LedgerAccount $ledgerAccount): bool
    {
        $this->ledgerAccount->reset();
        $this->ledgerAccount->fill($ledgerAccount);

        try {
            $this->ledgerAccount->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
