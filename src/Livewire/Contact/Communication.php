<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Actions\Communication\CreateCommunication;
use FluxErp\Actions\Communication\DeleteCommunication;
use FluxErp\Actions\Communication\UpdateCommunication;
use FluxErp\Actions\Printing;
use FluxErp\Actions\Tag\CreateTag;
use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Livewire\DataTables\CommunicationList;
use FluxErp\Livewire\Forms\CommunicationForm;
use FluxErp\Livewire\Forms\MediaForm;
use FluxErp\Mail\GenericMail;
use FluxErp\Models\Address;
use FluxErp\Models\Communication as CommunicationModel;
use FluxErp\Models\Contact;
use FluxErp\Models\MailAccount;
use FluxErp\Traits\Livewire\WithFileUploads;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Communication extends CommunicationList
{
    use WithFileUploads;

    protected string $view = 'flux::livewire.contact.communication';

    #[Modelable]
    public int $contactId;

    public CommunicationForm $communication;

    public MediaForm $attachments;

    #[Locked]
    public array $printLayouts = [];

    public array $selectedPrintLayouts = [];

    public function mount(): void
    {
        parent::mount();

        $this->printLayouts = array_keys((new CommunicationModel())->getPrintViews());
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('New'))
                ->icon('plus')
                ->color('primary')
                ->wireClick('edit')
                ->when(CreateCommunication::canPerformAction(false)),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->wireClick('edit(record.id)')
                ->when(UpdateCommunication::canPerformAction(false)),
            DataTableButton::make()
                ->label(__('Preview'))
                ->icon('document-text')
                ->color('primary')
                ->wireClick('createPreview(record.id)'),
            DataTableButton::make()
                ->label(__('Delete'))
                ->icon('trash')
                ->color('negative')
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:confirm.icon.error' => __(
                        'wire:confirm.delete',
                        ['model' => __('Communication')]
                    ),
                ])
                ->when(DeleteCommunication::canPerformAction(false)),
        ];
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'communicationTypes' => array_map(
                    fn ($item) => ['name' => $item, 'label' => __(Str::headline($item))],
                    CommunicationTypeEnum::values()
                ),
            ]
        );
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->whereRelation('contacts', 'communicatable_id', $this->contactId);
    }

    protected function getReturnKeys(): array
    {
        return array_merge(parent::getReturnKeys(), ['communication_type_enum']);
    }

    #[Renderless]
    public function save(): bool
    {
        $this->communication->communicatable_type = Contact::class;
        $this->communication->communicatable_id = $this->contactId;

        try {
            $this->communication->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->attachments->model_id = $this->communication->id;
        $this->attachments->model_type = CommunicationModel::class;
        $this->attachments->collection_name = 'attachments';
        $this->attachments->parent_id = null;
        try {
            $this->attachments->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function delete(CommunicationModel $communication): void
    {
        $this->communication->fill($communication);

        try {
            $this->communication->delete();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadData();
    }

    #[Renderless]
    public function send(): bool
    {
        $this->communication->attachments = $this->attachments->uploadedFile ?? [];

        if ($this->communication->mail_account_id) {
            $mailAccount = MailAccount::query()
                ->whereKey($this->communication->mail_account_id)
                ->first();

            config([
                'mail.default' => 'mail_account',
                'mail.mailers.mail_account.transport' => $mailAccount->smtp_mailer,
                'mail.mailers.mail_account.username' => $mailAccount->smtp_email,
                'mail.mailers.mail_account.password' => $mailAccount->smtp_password,
                'mail.mailers.mail_account.host' => $mailAccount->smtp_host,
                'mail.mailers.mail_account.port' => $mailAccount->smtp_port,
                'mail.mailers.mail_account.encryption' => $mailAccount->smtp_encryption,
                'mail.from.address' => $mailAccount->smtp_email,
                'mail.from.name' => auth()->user()->name,
            ]);
        }

        $this->communication->communicatable_type = Contact::class;
        $this->communication->communicatable_id = $this->contactId;

        try {
            Mail::to($this->communication->to)
                ->cc($this->communication->cc)
                ->bcc($this->communication->bcc)
                ->send(new GenericMail($this->communication));
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__('Email sent successfully!'));

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function edit(?CommunicationModel $communication = null): void
    {
        $this->communication->reset();
        $this->communication->fill($communication);

        $this->attachments->reset();
        if ($communication->id) {
            $this->attachments->fill($communication->getMedia('attachments'));
        }

        $this->js(<<<'JS'
            $openModal('edit-communication');
        JS);
    }

    #[Renderless]
    public function setTo(Address $address): void
    {
        $this->communication->to = [
            implode(
                "\n",
                array_filter([
                    $address->company,
                    trim($address->firstname . ' ' . $address->lastname),
                    $address->street,
                    trim($address->zip . ' ' . $address->city),
                ])
            ),
        ];
    }

    #[Renderless]
    public function addTag(string $name): void
    {
        try {
            $tag = CreateTag::make([
                'name' => $name,
                'type' => CommunicationModel::class,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->communication->tags[] = $tag->id;
        $this->js(<<<'JS'
            edit = true;
        JS);
    }

    #[Renderless]
    public function createPreview(?CommunicationModel $communication = null): void
    {
        $this->communication->reset();
        $this->communication->fill($communication);

        $this->attachments->reset();
        if ($communication->id) {
            $this->attachments->fill($communication->getMedia('attachments'));
        }

        $this->selectedPrintLayouts = [];

        $this->js(<<<'JS'
            $openModal('create-preview');
        JS);
    }

    #[Renderless]
    public function createDocuments(): ?BinaryFileResponse
    {
        $communication = CommunicationModel::query()
            ->whereKey($this->communication->id)
            ->with([
                'media' => fn ($query) => $query->where('collection_name', 'attachments')
                    ->select(['id', 'file_name']),
            ])
            ->first();

        // create the documents
        try {
            /** @var PrintableView $file */
            $file = Printing::make([
                'model_type' => CommunicationModel::class,
                'model_id' => $this->communication->id,
                'view' => 'communication',
            ])->checkPermission()->validate()->execute();

            $filename = $file->getSubject() . '.pdf';
            $file->savePDF($path = sys_get_temp_dir() . '/' . $filename);
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return null;
        }

        if ($this->selectedPrintLayouts['print']['communication'] ?? false) {
            // TODO: add to print queue for spooler
        }

        if ($this->selectedPrintLayouts['email']['communication'] ?? false) {
            $this->dispatch('create', $communication)->to('edit-mail');
        }

        if ($this->selectedPrintLayouts['download']['communication'] ?? false) {
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
