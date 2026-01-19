<?php

namespace FluxErp\Livewire;

use Exception;
use FluxErp\Actions\MailMessage\SendMail;
use FluxErp\Livewire\Forms\CommunicationForm;
use FluxErp\Models\EmailTemplate;
use FluxErp\Models\Language;
use FluxErp\Models\Media;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Laravel\SerializableClosure\SerializableClosure;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class EditMail extends Component
{
    use Actions, WithFileUploads;

    public array $emailTemplates = [];

    public array $files = [];

    public CommunicationForm $mailMessage;

    public array $mailMessages = [];

    public bool $multiple = false;

    public ?int $selectedTemplateId = null;

    public ?string $sessionKey = null;

    public ?array $templateData = null;

    public ?string $templateModelType = null;

    public array $groupedMailMessages = [];

    public array $groupKeys = [];

    #[Locked]
    public int $currentGroupIndex = 0;

    #[Locked]
    public array $editedGroups = [];

    #[Locked]
    public ?string $currentGroupLabel = null;

    #[Locked]
    public int $currentGroupRecipientCount = 0;

    protected $listeners = [
        'create',
        'createMany',
        'createFromSession',
    ];

    public function render(): View
    {
        return view('flux::livewire.edit-mail', [
            'mailAccounts' => array_merge(
                auth()
                    ->user()
                    ?->mailAccounts()
                    ->whereNotNull('smtp_email')
                    ->get(['mail_accounts.id', 'name'])
                    ->toArray() ?? [],
                [
                    ['id' => 'default', 'name' => __('Default')],
                ]
            ),
        ]);
    }

    #[Renderless]
    public function updatedFiles(): void
    {
        $files = array_map(fn (TemporaryUploadedFile $file) => [
            'name' => $file->getClientOriginalName(),
            'path' => $file->getRealPath(),
        ], $this->files);

        $this->mailMessage->attachments = array_merge($this->mailMessage->attachments, $files);
    }

    #[Renderless]
    public function create(
        array|CommunicationForm|Model $values,
        ?string $templateModelType = null,
        ?array $templateData = null,
        ?int $defaultTemplateId = null
    ): void {
        $this->multiple = false;
        $this->sessionKey = null;
        $this->reset('mailMessages');
        $this->templateModelType = $templateModelType;
        $this->templateData = $templateData;

        if ($values instanceof Model || is_array($values)) {
            $this->mailMessage->fill($values);
        } else {
            $this->mailMessage = $values;
        }

        $this->mailMessage->mail_account_id ??= auth()
            ->user()
            ->defaultMailAccount()
            ?->getKey() ?? 'default';

        $this->emailTemplates = resolve_static(EmailTemplate::class, 'query')
            ->when(
                $this->templateModelType,
                fn (Builder $query) => $query
                    ->where('model_type', $this->templateModelType)
                    ->orWhereNull('model_type')
            )
            ->get(['id', 'name'])
            ->toArray();

        if ($defaultTemplateId) {
            $this->selectedTemplateId = $defaultTemplateId;
            $this->applyTemplate();
        }

        $this->js(<<<'JS'
            $tallstackuiSelect('email-template').setOptions($wire.emailTemplates);
            $modalOpen('edit-mail');
        JS);
    }

    #[Renderless]
    public function createFromSession(string $key): void
    {
        $data = session()->get($key);

        if (Arr::isAssoc($data) || count($data) === 1) {
            $data = count($data) === 1 && Arr::isList($data) ? $data[0] : $data;
            session()->forget($key);

            $templateModelType = data_get($data, 'model_type');
            $templateData = data_get($data, 'template_data');
            $defaultTemplateId = data_get($data, 'default_template_id');

            if (! $templateData) {
                $bladeParameters = $this->getBladeParameters($data);
                if ($bladeParameters instanceof SerializableClosure) {
                    $templateData = $bladeParameters->getClosure()();
                }
            }

            $data['blade_parameters_serialized'] = false;
            $data['blade_parameters'] = null;

            $this->create($data, $templateModelType, $templateData, $defaultTemplateId);
        } else {
            $this->createMany($data);
            $this->sessionKey = $key;
        }
    }

    #[Renderless]
    public function createMany(Collection|array $mailMessages): void
    {
        $sessionKey = $this->sessionKey;

        if (count($mailMessages) === 1) {
            $data = Arr::first($mailMessages);
            if ($sessionKey) {
                session()->forget($sessionKey);
            }

            $bladeParameters = $this->getBladeParameters($data);
            $bladeParams = $bladeParameters instanceof SerializableClosure
                ? $bladeParameters->getClosure()()
                : $bladeParameters ?? [];

            $data['blade_parameters_serialized'] = false;
            $data['blade_parameters'] = null;

            $templateModelType = data_get($data, 'model_type');
            $templateData = data_get($data, 'template_data');
            $defaultTemplateId = data_get($data, 'default_template_id');

            $data = array_merge($data, $this->renderMessageContent($data, $bladeParams));
            $this->create($data, $templateModelType, $templateData, $defaultTemplateId);

            return;
        }

        $grouped = collect($mailMessages)->groupBy(
            fn (array $message) => (string) (data_get($message, 'group_key') ?? 'default')
        );

        if ($grouped->count() === 1) {
            $this->initializeSingleGroup($mailMessages, $sessionKey);

            return;
        }

        $this->groupedMailMessages = $grouped->toArray();
        $this->groupKeys = array_values(array_map('strval', array_keys($this->groupedMailMessages)));
        $this->currentGroupIndex = 0;
        $this->editedGroups = [];
        $this->sessionKey = $sessionKey;

        $this->loadCurrentGroup();
    }

    #[Renderless]
    public function cancelMultiGroup(): void
    {
        if ($this->sessionKey) {
            session()->forget($this->sessionKey);
        }

        $this->reset([
            'groupedMailMessages',
            'groupKeys',
            'currentGroupIndex',
            'currentGroupLabel',
            'currentGroupRecipientCount',
            'editedGroups',
            'mailMessages',
            'sessionKey',
        ]);

        $this->toast()
            ->warning(__('No emails sent'))
            ->send();

        $this->modalClose('edit-mail');
    }

    #[Renderless]
    public function nextGroup(): void
    {
        if ($this->currentGroupIndex >= count($this->groupKeys) - 1) {
            return;
        }

        $this->saveCurrentGroupState();
        $currentMailAccountId = $this->mailMessage->mail_account_id;

        $this->currentGroupIndex++;

        $this->loadCurrentGroup();
        $this->mailMessage->mail_account_id = $currentMailAccountId;

        $this->js(<<<'JS'
            $tallstackuiSelect('email-template').setOptions($wire.emailTemplates);
        JS);
    }

    #[Renderless]
    public function previousGroup(): void
    {
        if ($this->currentGroupIndex <= 0) {
            return;
        }

        $this->saveCurrentGroupState();
        $currentMailAccountId = $this->mailMessage->mail_account_id;

        $this->currentGroupIndex--;

        $this->loadCurrentGroup();
        $this->mailMessage->mail_account_id = $currentMailAccountId;

        $this->js(<<<'JS'
            $tallstackuiSelect('email-template').setOptions($wire.emailTemplates);
        JS);
    }

    #[Renderless]
    public function applyTemplate(): void
    {
        if (
            ! $this->selectedTemplateId
            || ! $template = resolve_static(EmailTemplate::class, 'query')
                ->whereKey($this->selectedTemplateId)
                ->first()
        ) {
            return;
        }

        if ($this->mailMessage->language_id) {
            $template->localize($this->mailMessage->language_id);
        }

        $renderedSubject = html_entity_decode($template->subject ?? '');
        $renderedHtmlBody = html_entity_decode($template->html_body ?? '');
        $renderedTextBody = html_entity_decode($template->text_body ?? '');
        $templateAttachments = $template->getMedia()
            ->map(fn (\Spatie\MediaLibrary\MediaCollections\Models\Media $media) => [
                'id' => $media->getKey(),
                'name' => $media->file_name,
            ])
            ->toArray();

        if (! $this->multiple && $this->templateData) {
            try {
                $renderedSubject = render_editor_blade($renderedSubject, $this->templateData);
                $renderedHtmlBody = render_editor_blade($renderedHtmlBody, $this->templateData);
                $renderedTextBody = render_editor_blade($renderedTextBody, $this->templateData);
            } catch (Exception $e) {
                $this->toast()
                    ->error(__('Template rendering failed: ') . $e->getMessage())
                    ->send();
            }
        }

        $fillData = [
            'cc' => array_merge($this->mailMessage->cc ?? [], $template->cc ?? []),
            'bcc' => array_merge($this->mailMessage->bcc ?? [], $template->bcc ?? []),
            'subject' => $renderedSubject,
            'html_body' => $renderedHtmlBody,
            'text_body' => $renderedTextBody,
            'attachments' => array_merge($this->mailMessage->attachments ?? [], $templateAttachments),
        ];

        if (! $this->multiple) {
            $fillData['to'] = $this->mailMessage->to ?: ($template->to ?? []);
        }

        $this->mailMessage->fill($fillData);
    }

    #[Renderless]
    public function clear(): void
    {
        $this->mailMessage->reset();
        $this->selectedTemplateId = null;

        $this->cleanupOldUploads();
    }

    #[Renderless]
    public function downloadAttachment(Media $media): BinaryFileResponse
    {
        return response()->download($media->getPath());
    }

    #[Renderless]
    public function send(): bool
    {
        if ($this->isMultiGroup()) {
            return $this->sendAllGroups();
        }

        return $this->sendSingleGroup();
    }

    protected function initializeSingleGroup(Collection|array $mailMessages, ?string $sessionKey): void
    {
        $firstMessage = Arr::first($mailMessages);
        $templateModelType = data_get($firstMessage, 'model_type');
        $templateData = data_get($firstMessage, 'template_data');

        $templateIds = collect($mailMessages)
            ->pluck('default_template_id')
            ->unique()
            ->filter();

        $defaultTemplateId = null;
        if ($templateIds->count() === 1) {
            $defaultTemplateId = $templateIds->first();
        }

        $this->create($firstMessage, $templateModelType, $templateData, $defaultTemplateId);
        $this->sessionKey = $sessionKey;
        $this->mailMessage->reset('attachments');

        if (! $sessionKey) {
            $this->mailMessages = $mailMessages;
        }

        $this->multiple = count($mailMessages) > 1;
    }

    protected function isMultiGroup(): bool
    {
        return count($this->groupKeys) > 1;
    }

    protected function loadCurrentGroup(): void
    {
        $currentKey = $this->groupKeys[$this->currentGroupIndex] ?? null;
        $currentMessages = $this->groupedMailMessages[$currentKey] ?? [];

        $firstMessage = Arr::first($currentMessages);
        $templateModelType = data_get($firstMessage, 'model_type');
        $templateData = data_get($firstMessage, 'template_data');

        $templateIds = collect($currentMessages)
            ->pluck('default_template_id')
            ->unique()
            ->filter();

        $defaultTemplateId = null;
        if ($templateIds->count() === 1) {
            $defaultTemplateId = $templateIds->first();
        }

        if (count($currentMessages) === 1) {
            $bladeParameters = $this->getBladeParameters($firstMessage);
            $bladeParams = $bladeParameters instanceof SerializableClosure
                ? $bladeParameters->getClosure()()
                : $bladeParameters ?? [];

            if (! $templateData && ! blank($bladeParams)) {
                $templateData = $bladeParams;
            }

            $firstMessage['blade_parameters_serialized'] = false;
            $firstMessage['blade_parameters'] = null;

            $firstMessage = array_merge($firstMessage, $this->renderMessageContent($firstMessage, $bladeParams));
        }

        $this->create($firstMessage, $templateModelType, $templateData, $defaultTemplateId);
        $this->mailMessage->reset('attachments');
        $this->mailMessages = $currentMessages;

        $this->multiple = count($currentMessages) > 1;

        $this->updateGroupDisplayProperties();
    }

    protected function saveCurrentGroupState(): void
    {
        $this->editedGroups[$this->currentGroupIndex] = [
            'mailMessage' => $this->mailMessage->toArray(),
            'mailMessages' => $this->mailMessages,
            'selectedTemplateId' => $this->selectedTemplateId,
        ];
    }

    protected function updateGroupDisplayProperties(): void
    {
        if (! $this->isMultiGroup()) {
            $this->currentGroupLabel = null;
            $this->currentGroupRecipientCount = count($this->mailMessages);

            return;
        }

        $currentKey = $this->groupKeys[$this->currentGroupIndex] ?? null;
        $currentMessages = $this->groupedMailMessages[$currentKey] ?? [];

        $this->currentGroupRecipientCount = count($currentMessages);

        $groupLabel = data_get(Arr::first($currentMessages), 'group_label');
        if ($groupLabel) {
            $this->currentGroupLabel = $groupLabel;

            return;
        }

        if (is_null($currentKey) || $currentKey === 'default') {
            $this->currentGroupLabel = __('Default');

            return;
        }

        $this->currentGroupLabel = resolve_static(Language::class, 'query')
            ->whereKey($currentKey)
            ->value('name') ?? __('Group') . ' ' . $currentKey;
    }

    protected function sendAllGroups(): bool
    {
        $this->saveCurrentGroupState();

        if ($this->sessionKey) {
            session()->forget($this->sessionKey);
        }

        $sharedMailAccountId = $this->mailMessage->mail_account_id;

        $totalSuccess = 0;
        $totalFailed = 0;

        foreach ($this->editedGroups as $group) {
            $groupMailMessage = data_get($group, 'mailMessage', []);
            $groupMailMessage['mail_account_id'] = $sharedMailAccountId;

            $result = $this->sendGroupMessages(
                $groupMailMessage,
                data_get($group, 'mailMessages', []),
                data_get($group, 'selectedTemplateId')
            );
            $totalSuccess += data_get($result, 'success', 0);
            $totalFailed += data_get($result, 'failed', 0);
        }

        $this->reset([
            'groupedMailMessages',
            'groupKeys',
            'currentGroupIndex',
            'currentGroupLabel',
            'currentGroupRecipientCount',
            'editedGroups',
            'mailMessages',
            'sessionKey',
        ]);

        $this->showSendResultToast($totalSuccess, $totalFailed);

        return $totalFailed === 0;
    }

    /**
     * @return array{success: int, failed: int}
     */
    protected function sendGroupMessages(
        array $editedMailMessage,
        array $mailMessages,
        ?int $selectedTemplateId
    ): array {
        $successCount = 0;
        $failedCount = 0;

        foreach ($mailMessages as $mailMessage) {
            try {
                $data = is_array($mailMessage)
                    ? $mailMessage
                    : $mailMessage->toArray();

                $editedWithoutTo = $editedMailMessage;
                unset(
                    $editedWithoutTo['to'],
                    $editedWithoutTo['communicatables'],
                    $editedWithoutTo['attachments'],
                    $editedWithoutTo['language_id']
                );
                $data = array_merge($data, array_filter($editedWithoutTo));

                $data['template_id'] = $selectedTemplateId;

                $bladeParams = data_get($mailMessage, 'blade_parameters');
                if (data_get($mailMessage, 'blade_parameters_serialized') && is_string($bladeParams)) {
                    $bladeParams = unserialize($bladeParams);
                }

                if ($bladeParams instanceof SerializableClosure) {
                    $data['blade_parameters'] = serialize($bladeParams);
                    $data['blade_parameters_serialized'] = true;
                } elseif (! is_null($bladeParams)) {
                    $data['blade_parameters'] = $bladeParams;
                }

                $data['queue'] = true;
                $data['mail_account_id'] = data_get($data, 'mail_account_id') === 'default'
                    ? null
                    : data_get($data, 'mail_account_id');

                SendMail::make($data)
                    ->checkPermission()
                    ->validate()
                    ->executeAsync();

                $successCount++;
            } catch (Throwable $e) {
                $failedCount++;
                exception_to_notifications(
                    exception: $e,
                    component: $this,
                    description: data_get($mailMessage, 'subject')
                );
            }
        }

        return ['success' => $successCount, 'failed' => $failedCount];
    }

    protected function sendSingleGroup(
        ?array $editedMailMessage = null,
        ?array $mailMessages = null,
        ?int $selectedTemplateId = null
    ): bool {
        $editedMailMessage ??= $this->mailMessage->toArray();
        $selectedTemplateId ??= $this->selectedTemplateId;

        if (! $mailMessages) {
            if (! $this->mailMessages && ! $this->sessionKey) {
                $mailMessages = [$this->mailMessage];
            } else {
                $mailMessages = $this->mailMessages ?: session()->pull($this->sessionKey);
            }
        }

        $single = count($mailMessages) === 1;
        $successCount = 0;
        $failedCount = 0;

        foreach ($mailMessages as $mailMessage) {
            try {
                $data = is_array($mailMessage)
                    ? $mailMessage
                    : $mailMessage->toArray();

                if (! $single) {
                    $editedWithoutTo = $editedMailMessage;
                    unset(
                        $editedWithoutTo['to'],
                        $editedWithoutTo['communicatables'],
                        $editedWithoutTo['attachments'],
                        $editedWithoutTo['language_id']
                    );
                    $data = array_merge($data, array_filter($editedWithoutTo));
                } else {
                    $data = array_merge($data, array_filter($editedMailMessage));
                }

                $data['template_id'] = $selectedTemplateId;

                $bladeParams = data_get($mailMessage, 'blade_parameters');
                if (data_get($mailMessage, 'blade_parameters_serialized') && is_string($bladeParams)) {
                    $bladeParams = unserialize($bladeParams);
                }

                if ($bladeParams instanceof SerializableClosure) {
                    $data['blade_parameters'] = serialize($bladeParams);
                    $data['blade_parameters_serialized'] = true;
                } elseif (! is_null($bladeParams)) {
                    $data['blade_parameters'] = $bladeParams;
                }

                $data['queue'] = ! $single;
                $data['mail_account_id'] = data_get($data, 'mail_account_id') === 'default'
                    ? null
                    : data_get($data, 'mail_account_id');

                $result = SendMail::make($data)
                    ->checkPermission()
                    ->validate()
                    ->when(
                        data_get($data, 'queue'),
                        fn (SendMail $action) => $action->executeAsync(),
                        fn (SendMail $action) => $action->execute()
                    );

                if (data_get($result, 'success') || data_get($data, 'queue')) {
                    $successCount++;
                } else {
                    $failedCount++;

                    if ($single) {
                        exception_to_notifications(
                            exception: new Exception(
                                data_get($result, 'error') ?? data_get($result, 'message')
                            ),
                            component: $this,
                            description: data_get($data, 'subject')
                        );

                        return false;
                    }
                }
            } catch (Throwable $e) {
                $failedCount++;
                exception_to_notifications(
                    exception: $e,
                    component: $this,
                    description: data_get($mailMessage, 'subject')
                );

                if ($single) {
                    return false;
                }
            }
        }

        $this->showSendResultToast($successCount, $failedCount);

        return $failedCount === 0;
    }

    protected function getBladeParameters(array|CommunicationForm $mailMessage): array|SerializableClosure|null
    {
        $bladeParameters = data_get($mailMessage, 'blade_parameters');

        if (data_get($mailMessage, 'blade_parameters_serialized') && is_string($bladeParameters)) {
            $bladeParameters = unserialize($bladeParameters);
        }

        return $bladeParameters;
    }

    protected function renderMessageContent(array $message, array|object $bladeParams): array
    {
        $renderedData = [];

        if (! blank(data_get($message, 'subject'))) {
            $renderedData['subject'] = render_editor_blade(
                html_entity_decode(data_get($message, 'subject')),
                $bladeParams
            );
        }

        if (! blank(data_get($message, 'html_body'))) {
            $renderedData['html_body'] = render_editor_blade(
                html_entity_decode(data_get($message, 'html_body')),
                $bladeParams
            );
        }

        if (! blank(data_get($message, 'text_body'))) {
            $renderedData['text_body'] = render_editor_blade(
                html_entity_decode(data_get($message, 'text_body')),
                $bladeParams
            );
        }

        return $renderedData;
    }

    protected function showSendResultToast(int $successCount, int $failedCount): void
    {
        if ($failedCount === 0) {
            $this->toast()
                ->success(__('Email(s) sent successfully!'))
                ->send();
        } elseif ($successCount === 0) {
            $this->toast()
                ->error(__('Failed to send emails!'))
                ->send();
        } else {
            $this->toast()
                ->warning(__(':success email(s) sent, :failed failed', [
                    'success' => $successCount,
                    'failed' => $failedCount,
                ]))
                ->send();
        }
    }
}
