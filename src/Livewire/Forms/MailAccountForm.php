<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\MailAccount\CreateMailAccount;
use FluxErp\Actions\MailAccount\DeleteMailAccount;
use FluxErp\Actions\MailAccount\UpdateMailAccount;
use FluxErp\Mail\GenericMail;
use FluxErp\Models\MailAccount;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Webklex\PHPIMAP\Exceptions\AuthFailedException;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use Webklex\PHPIMAP\Exceptions\ImapBadRequestException;
use Webklex\PHPIMAP\Exceptions\ImapServerErrorException;
use Webklex\PHPIMAP\Exceptions\ResponseException;
use Webklex\PHPIMAP\Exceptions\RuntimeException;

class MailAccountForm extends FluxForm
{
    public ?string $email = null;

    public string $encryption = 'ssl';

    public bool $has_valid_certificate = true;

    public ?string $host = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_auto_assign = false;

    public bool $is_o_auth = false;

    public ?string $name = null;

    public ?string $password = null;

    public int $port = 993;

    public ?string $protocol = 'imap';

    public ?string $smtp_email = null;

    public ?string $smtp_encryption = null;

    public ?string $smtp_from_name = null;

    public ?string $smtp_host = null;

    public ?string $smtp_mailer = 'smtp';

    public ?string $smtp_password = null;

    public ?int $smtp_port = 587;

    public ?string $smtp_reply_to = null;

    public ?string $smtp_user = null;

    public function save(): void
    {
        $this->smtp_email = $this->smtp_email ?: $this->email;

        parent::save();
    }

    #[Renderless]
    public function sendTestMail(?string $to = null): void
    {
        $to ??= auth()->user()->email;

        $mailAccount = app(MailAccount::class)->fill($this->toArray());
        $mailer = $mailAccount->mailer();

        $mailer->to($to)
            ->sendNow(
                GenericMail::make([
                    'subject' => __('Test mail'),
                    'html_body' => '<p>' . __('This is a test mail') . '</p>',
                ])
            );
    }

    /**
     * @throws ImapBadRequestException
     * @throws RuntimeException
     * @throws ResponseException
     * @throws ConnectionFailedException
     * @throws AuthFailedException
     * @throws ImapServerErrorException
     */
    public function testImapConnection(): void
    {
        app(MailAccount::class)->fill($this->toArray())->connect();
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateMailAccount::class,
            'update' => UpdateMailAccount::class,
            'delete' => DeleteMailAccount::class,
        ];
    }
}
