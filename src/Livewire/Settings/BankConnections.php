<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\BankConnectionList;
use FluxErp\Livewire\Forms\BankConnectionForm;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class BankConnections extends BankConnectionList
{
    use Actions;

    public BankConnectionForm $bankConnection;

    protected string $view = 'flux::livewire.settings.bank-connections';

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$wire.edit()',
                ]),
        ];
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->color('primary')
                ->icon('pencil')
                ->attributes([
                    'x-on:click' => '$wire.edit(record.id)',
                ]),
        ];
    }

    public function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'ledgerAccounts' => app(LedgerAccount::class)->query()
                ->select(['id', 'name'])
                ->get()
                ->toArray(),
            'currencies' => app(Currency::class)->query()
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
               $openModal('bank-connection-modal');
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
