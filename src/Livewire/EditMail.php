<?php

namespace FluxErp\Livewire;

use Exception;
use FluxErp\Livewire\Forms\CommunicationForm;
use FluxErp\Mail\GenericMail;
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
use Illuminate\Support\Facades\Mail;
use Laravel\SerializableClosure\SerializableClosure;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public ?string $templateType = null;

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
                    ->whereNotNull([
                        'smtp_email',
                        'smtp_password',
                        'smtp_host',
                        'smtp_port',
                    ])
                    ->get(['mail_accounts.id', 'email'])
                    ->toArray(),
                [
                    ['id' => null, 'email' => __('Default')],
                ]
            ),
        ]);
    }

    #[Renderless]
    public function applyTemplate(): void
    {
        if (! $this->selectedTemplateId) {
            return;
        }

        if ($this->multiple) {
            if (
                ! $template = resolve_static(EmailTemplate::class, 'query')
                    ->whereKey($this->selectedTemplateId)
                    ->first()
            ) {
                return;
            }

            $templateAttachments = $template->getMedia()
                ->map(fn (\Spatie\MediaLibrary\MediaCollections\Models\Media $media) => [
                    'name' => $media->file_name,
                    'id' => $media->getKey(),
                ]
                )
                ->toArray();

            $this->mailMessage->fill([
                'cc' => array_merge($this->mailMessage->cc ?? [], $template->cc ?? []),
                'bcc' => array_merge($this->mailMessage->bcc ?? [], $template->bcc ?? []),
                'subject' => html_entity_decode((string) ($template->subject ?? '')),
                'html_body' => html_entity_decode((string) ($template->html_body ?? '')),
                'text_body' => html_entity_decode((string) ($template->text_body ?? '')),
                'attachments' => array_merge($this->mailMessage->attachments ?? [], $templateAttachments),
            ]);

            return;
        }

        if (
            ! $template = resolve_static(EmailTemplate::class, 'query')
                ->whereKey($this->selectedTemplateId)
                ->first()
        ) {
            return;
        }

        $renderedSubject = (string) ($template->subject ?? '');
        $renderedHtmlBody = (string) ($template->html_body ?? '');
        $renderedTextBody = (string) ($template->text_body ?? '');

        if ($this->templateData && ! $this->multiple) {
            try {
                $renderedSubject = Blade::render(
                    html_entity_decode($template->subject ?? ''),
                    $this->templateData
                );
                $renderedHtmlBody = Blade::render(
                    html_entity_decode($template->html_body ?? ''),
                    $this->templateData
                );
                $renderedTextBody = Blade::render(
                    html_entity_decode($template->text_body ?? ''),
                    $this->templateData
                );
            } catch (Exception $e) {
                $this->notification()->error(__('Template rendering failed: ') . $e->getMessage())->send();
            }
        }

        $templateAttachments = $template->getMedia()
            ->map(
                fn (\Spatie\MediaLibrary\MediaCollections\Models\Media $media) => [
                    'name' => $media->file_name,
                    'id' => $media->getKey(),
                ]
            )
            ->toArray();

        $this->mailMessage->fill([
            'to' => $this->mailMessage->to ?: ($template->to ?? []),
            'cc' => array_merge($this->mailMessage->cc ?? [], $template->cc ?? []),
            'bcc' => array_merge($this->mailMessage->bcc ?? [], $template->bcc ?? []),
            'subject' => $renderedSubject,
            'html_body' => $renderedHtmlBody,
            'text_body' => $renderedTextBody,
            'attachments' => array_merge($this->mailMessage->attachments ?? [], $templateAttachments),
        ]);
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
        ?string $templateType = null,
        ?array $templateData = null,
        ?int $defaultTemplateId = null
    ): void {
        $this->multiple = false;
        $this->sessionKey = null;
        $this->reset('mailMessages');
        $this->templateType = $templateType;
        $this->templateData = $templateData;

        if ($values instanceof Model || is_array($values)) {
            $this->mailMessage->fill($values);
        } else {
            $this->mailMessage = $values;
        }

        $this->emailTemplates = resolve_static(EmailTemplate::class, 'query')
            ->when(
                $this->templateType,
                fn (Builder $query) => $query->where('model_type', $this->templateType)->orWhereNull('model_type')
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

            $templateType = data_get($data, 'model_type');
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

            $this->create($data, $templateType, $templateData, $defaultTemplateId);
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
            $data = $mailMessages[0];
            if ($sessionKey) {
                session()->forget($sessionKey);
            }

            $bladeParameters = $this->getBladeParameters($data);
            $bladeParams = $bladeParameters instanceof SerializableClosure
                ? $bladeParameters->getClosure()()
                : [];

            $data['blade_parameters_serialized'] = false;
            $data['blade_parameters'] = null;

            $templateType = data_get($data, 'model_type');
            $templateData = data_get($data, 'template_data');
            $defaultTemplateId = data_get($data, 'default_template_id');

            $renderedData = [];
            if (! blank($data['subject'])) {
                $renderedData['subject'] = Blade::render($data['subject'], $bladeParams);
            }
            if (! blank($data['html_body'])) {
                $renderedData['html_body'] = Blade::render($data['html_body'], $bladeParams);
            }
            if (! blank($data['text_body'])) {
                $renderedData['text_body'] = Blade::render($data['text_body'], $bladeParams);
            }

            $data = array_merge($data, $renderedData);
            $this->create($data, $templateType, $templateData, $defaultTemplateId);

            return;
        }

        $firstMessage = $mailMessages[0];
        $templateType = data_get($firstMessage, 'model_type');
        $templateData = data_get($firstMessage, 'template_data');

        $templateIds = collect($mailMessages)
            ->pluck('default_template_id')
            ->unique();
        $defaultTemplateId = $templateIds->count() === 1 && ! is_null($templateIds->first())
            ? $templateIds->first()
            : null;

        $this->create($firstMessage, $templateType, $templateData, $defaultTemplateId);
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
        if (! $single) {
            unset($editedMailMessage['to']);
        }

        $templateAttachments = [];
        if (
            $this->selectedTemplateId
            && $template = resolve_static(EmailTemplate::class, 'query')
                ->whereKey($this->selectedTemplateId)
                ->first()
        ) {
            $templateAttachments = $template->getMedia()
                ->map(
                    fn (\Spatie\MediaLibrary\MediaCollections\Models\Media $media) => [
                        'name' => $media->file_name,
                        'id' => $media->getKey(),
                    ]
                )
                ->toArray();
        }

        $bcc = $this->mailMessage->bcc;
        $cc = $this->mailMessage->cc;
        $exceptions = 0;

        foreach ($this->mailMessages as $mailMessage) {
            $bladeParameters = $this->getBladeParameters($mailMessage);

            if (! $mailMessage instanceof CommunicationForm) {
                $this->mailMessage->reset();

                $originalAttachments = $mailMessage['attachments'] ?? [];
                $mergedAttachments = array_merge($originalAttachments, $templateAttachments);

                $this->mailMessage->fill(array_merge(
                    $mailMessage,
                    array_filter($editedMailMessage),
                    [
                        'bcc' => $bcc,
                        'attachments' => $mergedAttachments,
                    ]
                ));
            }

            $mail = GenericMail::make($this->mailMessage, $bladeParameters);
            try {
                $message = Mail::to($this->mailMessage->to)
                    ->cc($cc)
                    ->bcc($bcc);

                if ($single) {
                    $message->send($mail);
                } else {
                    $message->queue($mail);
                }
            } catch (Exception $e) {
                exception_to_notifications(
                    exception: $e,
                    component: $this,
                    description: $this->mailMessage->subject
                );

                if ($this->multiple) {
                    $exceptions++;

                    continue;
                }

                return false;
            }
        }

        if ($exceptions === 0) {
            $this->notification()->success(__('Email(s) sent successfully!'))->send();
        }

        if (count($this->mailMessages) === $exceptions) {
            $this->notification()->error(__('Failed to send emails!'))->send();
        }

        return true;
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
