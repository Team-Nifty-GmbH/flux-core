<?php

namespace FluxErp\Livewire\Contact\Accounting;

use FluxErp\Actions\SepaMandate\CreateSepaMandate;
use FluxErp\Actions\SepaMandate\DeleteSepaMandate;
use FluxErp\Actions\SepaMandate\UpdateSepaMandate;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Livewire\DataTables\SepaMandateList;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Livewire\Forms\MediaUploadForm;
use FluxErp\Livewire\Forms\SepaMandateForm;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Media;
use FluxErp\Models\SepaMandate;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\CreatesDocuments;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class SepaMandates extends SepaMandateList
{
    use Actions, CreatesDocuments, WithFileUploads;

    #[Modelable]
    public ContactForm $contact;

    public SepaMandateForm $sepaMandate;

    public MediaUploadForm $signedMandate;

    protected ?string $includeBefore = 'flux::livewire.contact.accounting.sepa-mandates';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->wireClick('edit')
                ->when(resolve_static(CreateSepaMandate::class, 'canPerformAction', [false])),
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
                ->when(resolve_static(UpdateSepaMandate::class, 'canPerformAction', [false])),
            DataTableButton::make()
                ->text(__('Create Template'))
                ->icon('document-text')
                ->color('indigo')
                ->wireClick('createTemplate(record.id)'),
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Sepa Mandate')]),
                ])
                ->when(resolve_static(DeleteSepaMandate::class, 'canPerformAction', [false])),
        ];
    }

    #[Renderless]
    public function createDocuments(): null|MediaStream|Media
    {
        $response = $this->createDocumentFromItems(
            resolve_static(SepaMandate::class, 'query')
                ->whereKey($this->sepaMandate->id)
                ->first()
        );
        $this->loadData();

        return $response;
    }

    #[Renderless]
    public function createTemplate(?SepaMandate $sepaMandate = null): void
    {
        $this->sepaMandate->reset();
        $this->sepaMandate->fill($sepaMandate);

        $this->openCreateDocumentsModal();
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
            $modalOpen('edit-sepa-mandate-modal');
        JS);
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

        $this->signedMandate->model_type = app(SepaMandate::class)->getMorphClass();
        $this->signedMandate->model_id = $this->sepaMandate->id;
        $this->signedMandate->collection_name = 'signed_mandate';

        if ($this->signedMandate->stagedFiles || $this->signedMandate->id) {
            try {
                $this->signedMandate->save();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
            }
        }

        $this->loadData();

        return true;
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where('contact_id', $this->contact->id);
    }

    protected function getHtmlBody(OffersPrinting $item): string
    {
        return '';
    }

    protected function getPrintLayouts(): array
    {
        return resolve_static(SepaMandate::class, 'query')
            ->whereKey($this->sepaMandate->id)
            ->first(['id'])
            ->resolvePrintViews();
    }

    protected function getSubject(OffersPrinting $item): string
    {
        return __('Sepa Mandate');
    }

    protected function getTo(OffersPrinting $item, array $documents): array
    {
        return [$item->contact->invoiceAddress?->email_primary ?? $item->contact->mainAddress->email_primary];
    }

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'contactBankConnections' => resolve_static(ContactBankConnection::class, 'query')
                ->where('contact_id', $this->contact->id)
                ->get(['id', 'iban', 'bank_name']),
        ]);
    }
}
