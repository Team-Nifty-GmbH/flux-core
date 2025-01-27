<?php

namespace FluxErp\Mail;

use FluxErp\Actions\Printing;
use FluxErp\Livewire\Forms\CommunicationForm;
use FluxErp\Models\Client;
use FluxErp\Models\MailAccount;
use FluxErp\Models\Media;
use FluxErp\Traits\Makeable;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Blade;
use Laravel\SerializableClosure\SerializableClosure;
use Spatie\MediaLibrary\HasMedia;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GenericMail extends Mailable
{
    use Makeable, Queueable, SerializesModels;

    public function __construct(
        public Arrayable|array $mailMessageForm,
        public SerializableClosure|array|null $bladeParameters = null,
        public ?Client $client = null,
    ) {
        if ($this->mailMessageForm instanceof CommunicationForm) {
            $this->client ??= $this->mailMessageForm->communicatable()?->client;
        }

        $this->client ??= data_get($this->mailMessageForm, 'client_id')
            ? resolve_static(Client::class, 'query')
                ->whereKey(data_get($this->mailMessageForm, 'client_id'))
                ->first()
            : (
                $this->mailMessageForm instanceof CommunicationForm
                    ? $this->mailMessageForm->communicatable()?->client
                    : Client::default()
            );

        $this->mailMessageForm = $this->mailMessageForm instanceof Arrayable
            ? $this->mailMessageForm->toArray()
            : $this->mailMessageForm;
    }

    public function build(): void
    {
        if ($mailAccountId = data_get($this->mailMessageForm, 'mail_account_id')) {
            $mailAccount = resolve_static(MailAccount::class, 'query')
                ->whereKey($mailAccountId)
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
                'mail.from.name' => auth()->user()?->name,
            ]);
        }

        if ($this->bladeParameters) {
            $bladeParameters = $this->bladeParameters instanceof SerializableClosure
                ? $this->bladeParameters->getClosure()()
                : $this->bladeParameters;

            data_set(
                $this->mailMessageForm,
                'subject',
                Blade::render(
                    $this->mailMessageForm->subject,
                    $bladeParameters ?? []
                )
            );
            data_set(
                $this->mailMessageForm,
                'html_body',
                Blade::render(
                    $this->mailMessageForm->html_body,
                    $bladeParameters ?? []
                )
            );
            data_set(
                $this->mailMessageForm,
                'text_body',
                Blade::render(
                    $this->mailMessageForm->text_body,
                    $bladeParameters ?? []
                )
            );
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: data_get($this->mailMessageForm, 'subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: data_get($this->mailMessageForm, 'html_body'),
            text: data_get($this->mailMessageForm, 'text_body'),
            markdown: 'flux::emails.generic',
        );
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
                return Media::query()->whereKey($attachment['id'])->first();
            }

            if (is_int($attachment)) {
                return Media::query()->whereKey($attachment)->first();
            }

            return $attachment;
        }, data_get($this->mailMessageForm, 'attachments', [])));
    }
}
