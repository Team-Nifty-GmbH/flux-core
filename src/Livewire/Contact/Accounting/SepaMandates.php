<?php

namespace FluxErp\Livewire\Contact\Accounting;

use FluxErp\Actions\SepaMandate\CreateSepaMandate;
use FluxErp\Actions\SepaMandate\DeleteSepaMandate;
use FluxErp\Actions\SepaMandate\UpdateSepaMandate;
use FluxErp\Livewire\DataTables\SepaMandateList;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Livewire\Forms\SepaMandateForm;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\SepaMandate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class SepaMandates extends SepaMandateList
{
    use Actions;

    protected string $view = 'flux::livewire.contact.accounting.sepa-mandates';

    #[Modelable]
    public ContactForm $contact;

    public SepaMandateForm $sepaMandate;

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'contactBankConnections' => ContactBankConnection::query()
                ->where('contact_id', $this->contact->id)
                ->get(['id', 'iban', 'bank_name']),
        ]);
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->where('contact_id', $this->contact->id);
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('New'))
                ->icon('plus')
                ->color('primary')
                ->wireClick('edit')
                ->when(CreateSepaMandate::canPerformAction(false)),
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
                ->when(UpdateSepaMandate::canPerformAction(false)),
            DataTableButton::make()
                ->label(__('Delete'))
                ->icon('trash')
                ->color('negative')
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Sepa Mandate')]),
                ])
                ->when(DeleteSepaMandate::canPerformAction(false)),
        ];
    }

    public function save(): bool
    {
        $this->sepaMandate->client_id = $this->contact->client_id;
        $this->sepaMandate->contact_id = $this->contact->id;

        try {
            $this->sepaMandate->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function delete(SepaMandate $sepaMandate): void
    {
        $this->sepaMandate->fill($sepaMandate);

        try {
            $this->sepaMandate->delete();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadData();
    }

    public function edit(?SepaMandate $sepaMandate = null): void
    {
        $this->sepaMandate->reset();
        $this->sepaMandate->fill($sepaMandate);

        $this->js(<<<'JS'
            $openModal('edit-sepa-mandate');
        JS);
    }
}
