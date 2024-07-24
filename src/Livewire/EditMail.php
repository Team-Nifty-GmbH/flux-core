<?php

namespace FluxErp\Livewire;

use FluxErp\Livewire\Forms\CommunicationForm;
use FluxErp\Mail\GenericMail;
use FluxErp\Models\MailAccount;
use FluxErp\Models\Media;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use WireUi\Traits\Actions;

class EditMail extends Component
{
    use Actions, WithFileUploads;

    public CommunicationForm $mailMessage;

    public array $files = [];

    public array $mailMessages = [];

    public bool $multiple = false;

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
    public function create(array|CommunicationForm|Model $values): void
    {
        $this->multiple = false;
        $this->reset('mailMessages');

        if ($values instanceof Model || is_array($values)) {
            $this->mailMessage->fill($values);
        } else {
            $this->mailMessage = $values;
        }

        $this->js(<<<'JS'
            $openModal('edit-mail');
        JS);
    }

    #[Renderless]
    public function createMany(Collection|array $mailMessages): void
    {
        $this->create($mailMessages[0]);
        $this->mailMessage->reset('attachments');

        $this->mailMessages = $mailMessages;

        if (count($mailMessages) > 1) {
            $this->multiple = true;
        }
    }

    #[Renderless]
    public function createFromSession(string $key): void
    {
        $data = session()->pull($key);
        if (Arr::isAssoc($data)) {
            $this->create($data);
        } else {
            $this->createMany($data);
        }
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

    #[Renderless]
    public function send(): bool
    {
        if ($this->mailMessage->mail_account_id) {
            $mailAccount = resolve_static(MailAccount::class, 'query')
                ->whereKey($this->mailMessage->mail_account_id)
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

        if (! $this->mailMessages) {
            $this->mailMessages = [$this->mailMessage];
        }

        $bcc = $this->mailMessage->bcc;
        $cc = $this->mailMessage->cc;
        $baseMailMessage = clone $this->mailMessage;

        $exceptions = 0;

        foreach ($this->mailMessages as $mailMessage) {
            if (! $mailMessage instanceof CommunicationForm) {
                $this->mailMessage->reset();
                if (($mailMessage['blade_parameters_serialized'] ?? false)
                    && is_string($mailMessage['blade_parameters'])
                ) {
                    $bladeParameters = unserialize($mailMessage['blade_parameters']);
                } else {
                    $bladeParameters = $mailMessage['blade_parameters'] ?? [];
                }

                $this->mailMessage->fill(array_merge(
                    $mailMessage,
                    [
                        'bcc' => $bcc,
                        'subject' => Blade::render(
                            $baseMailMessage->subject,
                            $bladeParameters
                        ),
                        'html_body' => Blade::render(
                            $baseMailMessage->html_body,
                            $bladeParameters
                        ),
                    ]
                ));
            }

            try {
                Mail::to($this->mailMessage->to)
                    ->cc($cc)
                    ->bcc($bcc)
                    ->send(new GenericMail($this->mailMessage));
            } catch (\Exception $e) {
                exception_to_notifications($e, $this, description: $this->mailMessage->subject);

                if ($this->multiple) {
                    $exceptions++;

                    continue;
                }

                return false;
            }
        }

        if ($exceptions === 0) {
            $this->notification()->success(__('Email(s) sent successfully!'));
        }

        if (count($this->mailMessages) === $exceptions) {
            $this->notification()->error(__('Failed to send emails!'));
        }

        return true;
    }

    #[Renderless]
    public function downloadAttachment(Media $media): BinaryFileResponse
    {
        return response()->download($media->getPath());
    }

    #[Renderless]
    public function clear(): void
    {
        $this->mailMessage->reset();

        $this->cleanupOldUploads();
    }
}
