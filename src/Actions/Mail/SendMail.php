<?php

namespace FluxErp\Actions\Mail;

use FluxErp\Actions\FluxAction;
use FluxErp\Mail\GenericMail;
use FluxErp\Models\MailAccount;
use Illuminate\Support\Facades\Mail;

class SendMail extends FluxAction
{
    public static function models(): array
    {
        return [];
    }

    public function performAction(): mixed
    {
        if ($mailAccountId = $this->getData('mail_account_id')) {
            $mailAccount = app(MailAccount::class)->query()
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
                'mail.from.name' => auth()->user()->name,
            ]);
        }

        Mail::to($this->getData('to'))
            ->cc($this->getData('cc'))
            ->bcc($this->getData('bcc'))
            ->send(app(GenericMail::class, ['mailMessageForm' => $this->data]));
    }
}
