<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\BankConnectionList;
use FluxErp\Livewire\Forms\BankConnectionForm;
use FluxErp\Models\Currency;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;

class BankConnections extends BankConnectionList
{
    use Actions, DataTableHasFormEdit;

    #[DataTableForm('bank-connection-modal')]
    public BankConnectionForm $bankConnection;

    protected ?string $includeBefore = 'flux::livewire.settings.bank-connections';

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'currencies' => resolve_static(Currency::class, 'query')
                ->select(['id', 'name'])
                ->get()
                ->toArray(),
        ]);
    }
}
