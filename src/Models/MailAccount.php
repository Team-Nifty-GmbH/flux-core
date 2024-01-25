<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webklex\IMAP\Facades\Client as ImapClient;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Exceptions\AuthFailedException;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use Webklex\PHPIMAP\Exceptions\ImapBadRequestException;
use Webklex\PHPIMAP\Exceptions\ImapServerErrorException;
use Webklex\PHPIMAP\Exceptions\ResponseException;
use Webklex\PHPIMAP\Exceptions\RuntimeException;

class MailAccount extends Model
{
    use HasPackageFactory, HasUserModification, HasUuid;

    protected $casts = [
        'password' => 'encrypted',
        'smtp_password' => 'encrypted',
    ];

    protected $guarded = [
        'id',
    ];

    protected $hidden = [
        'password',
        'smtp_password',
    ];

    public function mailFolders(): HasMany
    {
        return $this->hasMany(MailFolder::class);
    }

    public function mailMessages(): HasMany
    {
        return $this->hasMany(Communication::class);
    }

    /**
     * @throws ImapBadRequestException
     * @throws RuntimeException
     * @throws ResponseException
     * @throws ConnectionFailedException
     * @throws AuthFailedException
     * @throws ImapServerErrorException
     */
    public function connect(): Client
    {
        return ImapClient::make([
            'host' => $this->host,
            'port' => $this->port,
            'encryption' => $this->encryption,
            'validate_cert' => $this->has_valid_certificate,
            'username' => $this->email,
            'password' => $this->password,
            'authentication' => $this->is_o_auth ? 'oauth' : null,
        ])->connect();
    }
}
