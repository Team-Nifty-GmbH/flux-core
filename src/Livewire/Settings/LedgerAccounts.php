<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\LedgerAccount\CreateLedgerAccount;
use FluxErp\Actions\LedgerAccount\DeleteLedgerAccount;
use FluxErp\Actions\LedgerAccount\UpdateLedgerAccount;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Livewire\DataTables\LedgerAccountList;
use FluxErp\Livewire\Forms\LedgerAccountForm;
use FluxErp\Models\LedgerAccount;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class LedgerAccounts extends LedgerAccountList
{
    use Actions;

    protected ?string $includeBefore = 'flux::livewire.settings.ledger-accounts';

    public LedgerAccountForm $ledgerAccount;

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->when(resolve_static(CreateLedgerAccount::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit()',
                ]),
        ];
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->when(resolve_static(UpdateLedgerAccount::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
            DataTableButton::make()
                ->label(__('Delete'))
                ->color('negative')
                ->icon('trash')
                ->when(resolve_static(DeleteLedgerAccount::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Ledger Account')]),
                ]),
        ];
    }

    public function getViewData(): array
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
            $openModal('edit-ledger-account');
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
