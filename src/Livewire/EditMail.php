<?php

namespace FluxErp\Livewire;

use Exception;
use FluxErp\Actions\MailMessage\SendMail;
use FluxErp\Livewire\Forms\CommunicationForm;
use FluxErp\Models\EmailTemplate;
use FluxErp\Models\Media;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Laravel\SerializableClosure\SerializableClosure;
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
                    ->mailAccounts()
                    ->whereNotNull('smtp_email')
                    ->get(['mail_accounts.id', 'name'])
                    ->toArray(),
                [
                    ['id' => 'default', 'name' => __('Default')],
                ]
            ),
        ]);
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
                $renderedSubject = Blade::render($renderedSubject, $this->templateData);
                $renderedHtmlBody = Blade::render($renderedHtmlBody, $this->templateData);
                $renderedTextBody = Blade::render($renderedTextBody, $this->templateData);
            } catch (Exception $e) {
                $this->notification()
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
                : [];

            $data['blade_parameters_serialized'] = false;
            $data['blade_parameters'] = null;

            $templateModelType = data_get($data, 'model_type');
            $templateData = data_get($data, 'template_data');
            $defaultTemplateId = data_get($data, 'default_template_id');

            $renderedData = [];

            if (! blank(data_get($data, 'subject'))) {
                $renderedData['subject'] = Blade::render(
                    html_entity_decode(data_get($data, 'subject')),
                    $bladeParams
                );
            }

            if (! blank(data_get($data, 'html_body'))) {
                $renderedData['html_body'] = Blade::render(
                    html_entity_decode(data_get($data, 'html_body')),
                    $bladeParams
                );
            }

            if (! blank(data_get($data, 'text_body'))) {
                $renderedData['text_body'] = Blade::render(
                    html_entity_decode(data_get($data, 'text_body')),
                    $bladeParams
                );
            }

            $data = array_merge($data, $renderedData);
            $this->create($data, $templateModelType, $templateData, $defaultTemplateId);

            return;
        }

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

    #[Renderless]
    public function downloadAttachment(Media $media): BinaryFileResponse
    {
        return response()->download($media->getPath());
    }

    #[Renderless]
    public function selectTemplate(EmailTemplate $template): void
    {
        $this->selectedTemplateId = $template->getKey();
        $this->applyTemplate();
    }

    #[Renderless]
    public function send(): bool
    {
        $editedMailMessage = $this->mailMessage->toArray();

        if (! $this->mailMessages && ! $this->sessionKey) {
            $this->mailMessages = [$this->mailMessage];
        } else {
            $this->mailMessages = $this->mailMessages ?: session()->pull($this->sessionKey);
        }

        $single = count($this->mailMessages) === 1;
        $successCount = 0;
        $failedCount = 0;

        foreach ($this->mailMessages as $mailMessage) {
            try {
                $data = is_array($mailMessage)
                    ? $mailMessage
                    : $mailMessage->toArray();

                if (! $single) {
                    $editedWithoutTo = $editedMailMessage;
                    unset($editedWithoutTo['to']);
                    $data = array_merge($data, array_filter($editedWithoutTo));
                } else {
                    $data = array_merge($data, array_filter($editedMailMessage));
                }

                $data['template_id'] = $this->selectedTemplateId;

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
                    ->when(data_get($data, 'queue'), fn (SendMail $action) => $action->executeAsync())
                    ->when(! data_get($data, 'queue'), fn (SendMail $action) => $action->execute());

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

        if ($failedCount === 0) {
            $this->notification()
                ->success(__('Email(s) sent successfully!'))
                ->send();
        } elseif ($successCount === 0) {
            $this->notification()
                ->error(__('Failed to send emails!'))
                ->send();
        } else {
            $this->notification()
                ->warning(__(':success email(s) sent, :failed failed', [
                    'success' => $successCount,
                    'failed' => $failedCount,
                ]))
                ->send();
        }

        return $failedCount === 0;
    }

    #[Renderless]
    public function updatedFiles(): void
    {
        $files = array_map(function ($file) {
            /** @var TemporaryUploadedFile $file */
            return [
                'name' => $file->getClientOriginalName(),
                'path' => $file->getRealPath(),
            ];
        }, $this->files);

        $this->mailMessage->attachments = array_merge($this->mailMessage->attachments, $files);
    }

    protected function getBladeParameters(array|CommunicationForm $mailMessage): array|SerializableClosure|null
    {
        $bladeParameters = data_get($mailMessage, 'blade_parameters');

        if (data_get($mailMessage, 'blade_parameters_serialized') && is_string($bladeParameters)) {
            $bladeParameters = unserialize($bladeParameters);
        }

        return $bladeParameters;
    }
}
