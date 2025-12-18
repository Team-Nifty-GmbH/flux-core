<?php

namespace FluxErp\Mail;

use FluxErp\Actions\Printing;
use FluxErp\Livewire\Forms\CommunicationForm;
use FluxErp\Models\Media;
use FluxErp\Models\Tenant;
use FluxErp\Traits\Makeable;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Laravel\SerializableClosure\SerializableClosure;
use Spatie\MediaLibrary\HasMedia;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GenericMail extends Mailable
{
    use Makeable, Queueable, SerializesModels;

    public function __construct(
        public Arrayable|array $mailMessageForm,
        public SerializableClosure|array|null $bladeParameters = null,
        public ?Tenant $tenant = null,
    ) {
        if ($this->mailMessageForm instanceof CommunicationForm) {
            $this->tenant ??= $this->mailMessageForm->communicatable()?->tenant;
        }

        $this->tenant ??= data_get($this->mailMessageForm, 'tenant_id')
            ? resolve_static(Tenant::class, 'query')
                ->whereKey(data_get($this->mailMessageForm, 'tenant_id'))
                ->first()
            : resolve_static(Tenant::class, 'default');

        $this->mailMessageForm = $this->mailMessageForm instanceof Arrayable
            ? $this->mailMessageForm->toArray()
            : $this->mailMessageForm;
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return array_filter(array_map(function ($attachment) {
            if (is_array($attachment)
                && ($modelType = data_get($attachment, 'model_type'))
                && ($modelId = data_get($attachment, 'model_id'))
                && ($view = data_get($attachment, 'view'))
            ) {
                $model = morph_to($modelType, $modelId);
                if ($attachRelation = data_get($attachment, 'attach_relation')) {
                    $model = data_get($model, $attachRelation);
                }

                /** @var PrintableView $action */
                $action = Printing::make([
                    'model_type' => $modelType,
                    'model_id' => $modelId,
                    'view' => $view,
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();

                if (is_a($model, HasMedia::class) && $action->shouldStore()) {
                    $attachment = $action->attachToModel($model);
                } else {
                    return Attachment::fromData(fn () => $action->pdf->output(), $action->getFileName());
                }
            }

            if ($attachment instanceof Media) {
                return $attachment;
            }

            if (is_array($attachment) && ($attachment['path'] ?? false)) {
                return Attachment::fromPath($attachment['path'])->as($attachment['name'] ?? null);
            }

            if (is_a($attachment, UploadedFile::class)) {
                return Attachment::fromPath($attachment->getRealPath())->as($attachment->getClientOriginalName());
            }

            if (is_array($attachment) && ($attachment['id'] ?? false)) {
                return resolve_static(Media::class, 'query')->whereKey($attachment['id'])->first();
            }

            if (is_int($attachment)) {
                return resolve_static(Media::class, 'query')->whereKey($attachment)->first();
            }

            return $attachment;
        }, data_get($this->mailMessageForm, 'attachments', [])));
    }

    public function build(): void
    {
        if ($this->bladeParameters) {
            $bladeParameters = $this->bladeParameters instanceof SerializableClosure
                ? $this->bladeParameters->getClosure()()
                : $this->bladeParameters;

            data_set(
                $this->mailMessageForm,
                'subject',
                render_editor_blade(
                    data_get($this->mailMessageForm, 'subject'),
                    $bladeParameters ?? []
                )
            );
            data_set(
                $this->mailMessageForm,
                'html_body',
                render_editor_blade(
                    data_get($this->mailMessageForm, 'html_body'),
                    $bladeParameters ?? []
                )
            );
            data_set(
                $this->mailMessageForm,
                'text_body',
                render_editor_blade(
                    data_get($this->mailMessageForm, 'text_body'),
                    $bladeParameters ?? []
                )
            );
        }
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'flux::emails.generic',
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: data_get($this->mailMessageForm, 'subject'),
        );
    }
}
