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
use FluxErp\Livewire\Forms\MediaUploadForm;
use FluxErp\Mail\GenericMail;
use FluxErp\Models\Address;
use FluxErp\Models\Communication as CommunicationModel;
use FluxErp\Models\MailAccount;
use FluxErp\Models\Media;
use FluxErp\Traits\Communicatable;
use FluxErp\Traits\Livewire\CreatesDocuments;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Scout\Searchable;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Communication extends CommunicationList
{
    use CreatesDocuments, WithFileUploads;

    public MediaUploadForm $attachments;

    public CommunicationForm $communication;

    #[Modelable]
    public ?int $modelId = null;

    protected ?string $includeBefore = 'flux::livewire.features.communications.communication';

    protected ?string $modelType = null;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->wireClick('edit')
                ->when(resolve_static(CreateCommunication::class, 'canPerformAction', [false])),
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
                ->when(resolve_static(UpdateCommunication::class, 'canPerformAction', [false])),
            DataTableButton::make()
                ->text(__('Preview'))
                ->icon('document-text')
                ->color('indigo')
                ->wireClick('createPreview(record.id)'),
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __(
                        'wire:confirm.delete',
                        ['model' => __('Communication')]
                    ),
                ])
                ->when(resolve_static(DeleteCommunication::class, 'canPerformAction', [false])),
        ];
    }

    #[Renderless]
    public function addCommunicatable(string $modelType, string|int $modelId): void
    {
        $model = morph_to($modelType, $modelId);
        $this->communication->communicatables[] = [
            'communicatable_type' => $modelType,
            'communicatable_id' => $modelId,
            'href' => method_exists($model, 'getUrl') ? $model->getUrl() : null,
            'label' => __(Str::headline($modelType)) . ': '
                . (method_exists($model, 'getLabel') ? $model->getLabel() : $model->getKey()),
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

    #[Computed(cache: true)]
    public function communicatables(): array
    {
        return collect(
            array_filter(
                Relation::morphMap(),
                function (string $class, string $alias) {
                    $uses = class_uses_recursive($class);

                    return in_array(Communicatable::class, $uses)
                        && in_array(Searchable::class, $uses);
                },
                ARRAY_FILTER_USE_BOTH
            )
        )
            ->mapWithKeys(fn ($value, $key) => [$key => ['value' => $key, 'label' => __(Str::headline($key))]])
            ->toArray();
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
    public function edit(?CommunicationModel $communication = null): void
    {
        $this->communication->reset();
        $this->communication->fill($communication);

        $this->attachments->reset();
        if ($communication->id) {
            $this->attachments->fill($communication->getMedia('attachments'));
        }

        $this->js(<<<'JS'
            $modalOpen('edit-communication');
        JS);
    }

    #[Renderless]
    public function save(): bool
    {
        if (! $this->communication->communicatables && $this->modelType && $this->modelId) {
            $this->communication->communicatables[] = [
                'communicatable_type' => morph_alias($this->modelType),
                'communicatable_id' => $this->modelId,
            ];
        }

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

        $this->notification()->success(__('Email sent successfully!'))->send();

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function setTo(Address $address): void
    {
        $this->communication->communicatables = [
            [
                'communicatable_type' => $address->getMorphClass(),
                'communicatable_id' => $address->id,
                'href' => $address->getUrl(),
                'label' => __(Str::headline($address->getMorphClass())) . ': ' . $address->getLabel(),
            ],
        ];

        $this->communication->to = [
            implode("\n", $address->postal_address),
        ];
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder
            ->when(
                $this->modelId && $this->modelType,
                fn (Builder $query) => $query->whereHas(
                    'communicatables',
                    fn ($query) => $query->where('communicatable_id', $this->modelId)
                        ->where('communicatable_type', morph_alias($this->modelType))
                )
            );
    }

    protected function getHtmlBody(OffersPrinting $item): string
    {
        return $item->html_body ?? $item->text_body ?? '';
    }

    protected function getPrintLayouts(): array
    {
        return resolve_static(CommunicationModel::class, 'query')
            ->whereKey($this->communication->id)
            ->first(['id'])
            ->resolvePrintViews();
    }

    protected function getReturnKeys(): array
    {
        return array_merge(parent::getReturnKeys(), ['communication_type_enum']);
    }

    protected function getSubject(OffersPrinting $item): string
    {
        return $item->subject;
    }

    protected function getTo(OffersPrinting $item, array $documents): array
    {
        return Arr::wrap($item->to);
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
}
