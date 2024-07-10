<?php

namespace FluxErp\Livewire\Contact\Accounting;

use FluxErp\Actions\ContactBankConnection\CreateContactBankConnection;
use FluxErp\Actions\ContactBankConnection\DeleteContactBankConnection;
use FluxErp\Actions\ContactBankConnection\UpdateContactBankConnection;
use FluxErp\Livewire\DataTables\ContactBankConnectionList as BaseContactBankConnectionList;
use FluxErp\Livewire\Forms\ContactBankConnectionForm;
use FluxErp\Models\ContactBankConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class BankConnections extends BaseContactBankConnectionList
{
    use Actions;

    protected string $view = 'flux::livewire.contact.accounting.bank-connections';

    #[Modelable]
    public int $contactId;

    public ContactBankConnectionForm $contactBankConnection;

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where('contact_id', $this->contactId);
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('New'))
                ->icon('plus')
                ->color('primary')
                ->wireClick('edit')
                ->when(resolve_static(CreateContactBankConnection::class, 'canPerformAction', [false])),
        ];
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->wireClick('edit(record.id)')
                ->when(resolve_static(UpdateContactBankConnection::class, 'canPerformAction', [false])),
            DataTableButton::make()
                ->label(__('Delete'))
                ->icon('trash')
                ->color('negative')
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Bank connection')]),
                ])
                ->when(resolve_static(DeleteContactBankConnection::class, 'canPerformAction', [false])),
        ];
    }

    public function save(): bool
    {
        $this->contactBankConnection->contact_id = $this->contactId;

        try {
            $this->contactBankConnection->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function delete(ContactBankConnection $contactBankConnection): void
    {
        $this->contactBankConnection->fill($contactBankConnection);

        try {
            $this->contactBankConnection->delete();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadData();
    }

    public function edit(?ContactBankConnection $contactBankConnection = null): void
    {
        $this->contactBankConnection->reset();
        $this->contactBankConnection->fill($contactBankConnection);

        $this->js(<<<'JS'
            $openModal('edit-contact-bank-connection');
        JS);
    }
}
