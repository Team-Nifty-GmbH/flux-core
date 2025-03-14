<?php

namespace FluxErp\Models;

use FluxErp\Events\MailAccount\Connecting;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webklex\IMAP\Facades\Client as ImapClient;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Exceptions\AuthFailedException;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use Webklex\PHPIMAP\Exceptions\ImapBadRequestException;
use Webklex\PHPIMAP\Exceptions\ImapServerErrorException;
use Webklex\PHPIMAP\Exceptions\ResponseException;
use Webklex\PHPIMAP\Exceptions\RuntimeException;

class MailAccount extends FluxModel
{
    use HasPackageFactory, HasUserModification, HasUuid, LogsActivity;

    public bool $supportsHierarchicalFolders = true;

    protected $hidden = [
        'password',
        'smtp_password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'smtp_password' => 'encrypted',
        ];
    }

    /**
     * @throws ImapBadRequestException
     * @throws RuntimeException
     * @throws ResponseException
     * @throws ConnectionFailedException
     * @throws AuthFailedException
     * @throws ImapServerErrorException
     */
    public function connect(): ?Client
    {
        event(new Connecting($this));

        try {
            return ImapClient::make([
                'host' => $this->host,
                'port' => $this->port,
                'encryption' => $this->encryption,
                'validate_cert' => $this->has_valid_certificate,
                'username' => $this->email,
                'password' => $this->password,
                'authentication' => $this->is_o_auth ? 'oauth' : null,
            ])->connect();
        } catch (AuthFailedException $e) {
            logger($e->getMessage(), ['mail_account_id' => $this->id]);
        }

        return null;
    }

    public function mailFolders(): HasMany
    {
        return $this->hasMany(MailFolder::class);
    }

    public function mailMessages(): HasMany
    {
        return $this->hasMany(Communication::class);
    }
}
