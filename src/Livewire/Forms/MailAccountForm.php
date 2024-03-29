<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\MailAccount\CreateMailAccount;
use FluxErp\Actions\MailAccount\DeleteMailAccount;
use FluxErp\Actions\MailAccount\UpdateMailAccount;
use FluxErp\Models\MailAccount;
use Livewire\Attributes\Locked;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Webklex\PHPIMAP\Exceptions\AuthFailedException;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use Webklex\PHPIMAP\Exceptions\ImapBadRequestException;
use Webklex\PHPIMAP\Exceptions\ImapServerErrorException;
use Webklex\PHPIMAP\Exceptions\ResponseException;
use Webklex\PHPIMAP\Exceptions\RuntimeException;

class MailAccountForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $protocol = 'imap';

    public ?string $email = null;

    public ?string $password = null;

    public ?string $host = null;

    public int $port = 993;

    public string $encryption = 'ssl';

    public ?string $smtp_email = null;

    public ?string $smtp_password = null;

    public ?string $smtp_host = null;

    public int $smtp_port = 587;

    public ?string $smtp_encryption = null;

    public bool $is_auto_assign = false;

    public bool $is_o_auth = false;

    public bool $has_valid_certificate = true;

    protected function getActions(): array
    {
        return [
            'create' => CreateMailAccount::class,
            'update' => UpdateMailAccount::class,
            'delete' => DeleteMailAccount::class,
        ];
    }

    public function save(): void
    {
        $this->smtp_email = $this->smtp_email ?: $this->email;

        parent::save();
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

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function testSmtpConnection(): void
    {
        $transport = new EsmtpTransport($this->smtp_host, $this->smtp_port);
        $transport->setUsername($this->smtp_email);
        $transport->setPassword($this->smtp_password);

        $transport->start();
    }
}
