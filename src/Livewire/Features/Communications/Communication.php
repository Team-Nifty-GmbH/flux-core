<?php

namespace FluxErp\Livewire\Features\Communications;

use FluxErp\Actions\Communication\CreateCommunication;
use FluxErp\Actions\Communication\DeleteCommunication;
use FluxErp\Actions\Communication\UpdateCommunication;
use FluxErp\Actions\Tag\CreateTag;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Livewire\DataTables\CommunicationList;
use FluxErp\Livewire\Forms\CommunicationForm;
use FluxErp\Livewire\Forms\MediaForm;
use FluxErp\Mail\GenericMail;
use FluxErp\Models\Address;
use FluxErp\Models\Communication as CommunicationModel;
use FluxErp\Models\MailAccount;
use FluxErp\Models\Media;
use FluxErp\Traits\Livewire\CreatesDocuments;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Communication extends CommunicationList
{
    use CreatesDocuments, WithFileUploads;

    protected ?string $includeBefore = 'flux::livewire.features.communications.communication';

    #[Modelable]
    public int $modelId;

    protected ?string $modelType = null;

    public CommunicationForm $communication;

    public MediaForm $attachments;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('New'))
                ->icon('plus')
                ->color('primary')
                ->wireClick('edit')
                ->when(resolve_static(CreateCommunication::class, 'canPerformAction', [false])),
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
                ->when(resolve_static(UpdateCommunication::class, 'canPerformAction', [false])),
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
                    'wire:flux-confirm.icon.error' => __(
                        'wire:confirm.delete',
                        ['model' => __('Communication')]
                    ),
                ])
                ->when(resolve_static(DeleteCommunication::class, 'canPerformAction', [false])),
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

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->whereHas(
            'communicatables',
            fn ($query) => $query->where('communicatable_id', $this->modelId)
                ->where('communicatable_type', morph_alias($this->modelType))
        );
    }

    protected function getReturnKeys(): array
    {
        return array_merge(parent::getReturnKeys(), ['communication_type_enum']);
    }

    #[Renderless]
    public function save(): bool
    {
        $this->communication->communicatable_type ??= morph_alias($this->modelType);
        $this->communication->communicatable_id ??= $this->modelId;

        try {
            $this->communication->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->attachments->model_id = $this->communication->id;
        $this->attachments->model_type = morph_alias(CommunicationModel::class);
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
            $mailAccount = resolve_static(MailAccount::class, 'query')
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

        $this->communication->communicatable_type = morph_alias($this->modelType);
        $this->communication->communicatable_id = $this->modelId;

        try {
            Mail::to($this->communication->to)
                ->cc($this->communication->cc)
                ->bcc($this->communication->bcc)
                ->send(GenericMail::make($this->communication));
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
        $this->communication->communicatable_type = morph_alias(Address::class);
        $this->communication->communicatable_id = $address->id;

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
                'type' => morph_alias(CommunicationModel::class),
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

        $this->openCreateDocumentsModal();
    }

    #[Renderless]
    public function createDocuments(): null|MediaStream|Media
    {
        $response = $this->createDocumentFromItems(
            resolve_static(CommunicationModel::class, 'query')
                ->whereKey($this->communication->id)
                ->first()
        );
        $this->loadData();

        return $response;
    }

    protected function getTo(OffersPrinting $item, array $documents): array
    {
        return Arr::wrap($item->to);
    }

    protected function getSubject(OffersPrinting $item): string
    {
        return $item->subject;
    }

    protected function getHtmlBody(OffersPrinting $item): string
    {
        return $item->html_body ?? $item->text_body ?? '';
    }

    protected function getPrintLayouts(): array
    {
        return array_keys(
            resolve_static(CommunicationModel::class, 'query')
                ->whereKey($this->communication->id)
                ->first(['id'])
                ->resolvePrintViews()
        );
    }
}
