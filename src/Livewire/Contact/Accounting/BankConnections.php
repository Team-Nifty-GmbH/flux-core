<?php

namespace FluxErp\Livewire\Contact\Accounting;

use FluxErp\Actions\ContactBankConnection\CreateContactBankConnection;
use FluxErp\Actions\ContactBankConnection\DeleteContactBankConnection;
use FluxErp\Actions\ContactBankConnection\UpdateContactBankConnection;
use FluxErp\Livewire\DataTables\ContactBankConnectionList as BaseContactBankConnectionList;
use FluxErp\Livewire\Forms\ContactBankConnectionForm;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class BankConnections extends BaseContactBankConnectionList
{
    use Actions;

    public ContactBankConnectionForm $contactBankConnection;

    #[Modelable]
    public int $contactId;

    protected string $view = 'flux::livewire.contact.accounting.bank-connections';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->wireClick('edit')
                ->when(resolve_static(CreateContactBankConnection::class, 'canPerformAction', [false])),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->wireClick('edit(record.id)')
                ->when(resolve_static(UpdateContactBankConnection::class, 'canPerformAction', [false])),
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Bank connection')]),
                ])
                ->when(resolve_static(DeleteContactBankConnection::class, 'canPerformAction', [false])),
        ];
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
            $modalOpen('edit-contact-bank-connection');
        JS);
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

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where('contact_id', $this->contactId);
    }
}
