<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\BankConnectionList;
use FluxErp\Livewire\Forms\BankConnectionForm;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class BankConnections extends BankConnectionList
{
    use Actions;

    public BankConnectionForm $bankConnection;

    protected ?string $includeBefore = 'flux::livewire.settings.bank-connections';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$wire.edit()',
                ]),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->color('indigo')
                ->icon('pencil')
                ->attributes([
                    'x-on:click' => '$wire.edit(record.id)',
                ]),
        ];
    }

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'ledgerAccounts' => resolve_static(LedgerAccount::class, 'query')
                ->select(['id', 'name'])
                ->get()
                ->toArray(),
            'currencies' => resolve_static(Currency::class, 'query')
                ->select(['id', 'name'])
                ->get()
                ->toArray(),
        ]);
    }

    public function edit(?BankConnection $record = null): void
    {
        $this->bankConnection->reset();
        $this->bankConnection->fill($record);

        $this->js(
            <<<'JS'
               $modalOpen('bank-connection-modal');
            JS
        );
    }

    public function save(): bool
    {
        try {
            $this->bankConnection->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
