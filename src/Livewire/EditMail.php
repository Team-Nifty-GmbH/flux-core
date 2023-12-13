<?php

namespace FluxErp\Livewire;

use FluxErp\Livewire\Forms\MailMessageForm;
use FluxErp\Mail\GenericMail;
use FluxErp\Models\MailAccount;
use FluxErp\Models\Media;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use WireUi\Traits\Actions;

class EditMail extends Component
{
    use Actions, WithFileUploads;

    public MailMessageForm $mailMessage;

    public array $files = [];

    protected $listeners = [
        'create',
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
                [['id' => null, 'email' => __('Default')]]),
        ]);
    }

    #[Renderless]
    public function create(array|MailMessageForm|Model $values): void
    {
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
            $mailAccount = MailAccount::query()->whereKey($this->mailMessage->mail_account_id)->first();
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

        try {
            Mail::to($this->mailMessage->to)
                ->cc($this->mailMessage->cc)
                ->bcc($this->mailMessage->bcc)
                ->send(new GenericMail($this->mailMessage));
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__('Email sent successfully!'));

        return true;
    }

    #[Renderless]
    public function downloadAttachment(Media $media): BinaryFileResponse
    {
        return response()->download($media->getPath());
    }

    #[Renderless]
    public function clear()
    {
        $this->mailMessage->reset();

        $this->cleanupOldUploads();
    }
}
