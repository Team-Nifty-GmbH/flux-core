<?php

namespace FluxErp\Livewire\Contact\Accounting;

use FluxErp\Actions\Printing;
use FluxErp\Actions\SepaMandate\CreateSepaMandate;
use FluxErp\Actions\SepaMandate\DeleteSepaMandate;
use FluxErp\Actions\SepaMandate\UpdateSepaMandate;
use FluxErp\Livewire\DataTables\SepaMandateList;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Livewire\Forms\MediaForm;
use FluxErp\Livewire\Forms\SepaMandateForm;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\SepaMandate;
use FluxErp\Traits\Livewire\WithFileUploads;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class SepaMandates extends SepaMandateList
{
    use Actions, WithFileUploads;

    protected string $view = 'flux::livewire.contact.accounting.sepa-mandates';

    #[Modelable]
    public ContactForm $contact;

    public SepaMandateForm $sepaMandate;

    public MediaForm $signedMandate;

    #[Locked]
    public array $printLayouts = [];

    public array $selectedPrintLayouts = [];

    public function mount(): void
    {
        parent::mount();

        $this->printLayouts = [
            'sepa-mandate',
        ];
    }

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'contactBankConnections' => app(ContactBankConnection::class)->query()
                ->where('contact_id', $this->contact->id)
                ->get(['id', 'iban', 'bank_name']),
        ]);
    }

    protected function getBuilder(Builder $builder): Builder
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
                ->when(resolve_static(CreateSepaMandate::class, 'canPerformAction', [false])),
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
                ->when(resolve_static(UpdateSepaMandate::class, 'canPerformAction', [false])),
            DataTableButton::make()
                ->label(__('Create Template'))
                ->icon('document-text')
                ->color('primary')
                ->wireClick('createTemplate(record.id)'),
            DataTableButton::make()
                ->label(__('Delete'))
                ->icon('trash')
                ->color('negative')
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Sepa Mandate')]),
                ])
                ->when(resolve_static(DeleteSepaMandate::class, 'canPerformAction', [false])),
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

    public function createTemplate(?SepaMandate $sepaMandate = null): void
    {
        $this->sepaMandate->reset();
        $this->sepaMandate->fill($sepaMandate);

        $this->selectedPrintLayouts = [];

        $this->js(<<<'JS'
            $openModal('create-documents');
        JS);
    }

    #[Renderless]
    public function createDocuments(): ?BinaryFileResponse
    {
        $sepaMandate = app(SepaMandate::class)->query()
            ->whereKey($this->sepaMandate->id)
            ->with([
                'contact.mainAddress',
            ])
            ->first();

        // create the documents
        try {
            /** @var PrintableView $file */
            $file = Printing::make([
                'model_type' => app(SepaMandate::class)->getMorphClass(),
                'model_id' => $this->sepaMandate->id,
                'view' => 'sepa-mandate',
            ])->checkPermission()->validate()->execute();

            $filename = $file->getSubject() . '.pdf';
            $file->savePDF($path = sys_get_temp_dir() . '/' . $filename);
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return null;
        }

        if ($this->selectedPrintLayouts['print']['sepa-mandate'] ?? false) {
            // TODO: add to print queue for spooler
        }

        if ($this->selectedPrintLayouts['email']['sepa-mandate'] ?? false) {
            $to[] = $sepaMandate->contact->mainAddress->email_primary;

            $this->dispatch(
                'create',
                [
                    'to' => $to,
                    'subject' => __('Sepa Mandate'),
                    'communicatable_type' => morph_alias(Contact::class),
                    'communicatable_id' => $this->contact->id,
                    'attachments' => [
                        [
                            'name' => $filename,
                            'path' => $path,
                        ],
                    ],
                ]
            )->to('edit-mail');
        }

        if ($this->selectedPrintLayouts['download']['sepa-mandate'] ?? false) {
            $headers = [
                'Content-type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename=' . $filename,
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            return response()->download($path, $filename, $headers);
        }

        return null;
    }
}
