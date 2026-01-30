<?php

namespace FluxErp\Models;

use FluxErp\Actions\MailFolder\CreateMailFolder;
use FluxErp\Actions\MailFolder\DeleteMailFolder;
use FluxErp\Actions\MailFolder\UpdateMailFolder;
use FluxErp\Events\MailAccount\Connecting;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;
use Webklex\IMAP\Facades\Client as ImapClient;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Exceptions\AuthFailedException;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use Webklex\PHPIMAP\Exceptions\ImapBadRequestException;
use Webklex\PHPIMAP\Exceptions\ImapServerErrorException;
use Webklex\PHPIMAP\Exceptions\ResponseException;
use Webklex\PHPIMAP\Exceptions\RuntimeException;
use Webklex\PHPIMAP\Folder;

class MailAccount extends FluxModel
{
    use HasPackageFactory, HasUserModification, HasUuid, LogsActivity;

    public bool $supportsHierarchicalFolders = true;

    protected $hidden = [
        'password',
        'smtp_password',
    ];

    private ?Client $imapClient = null;

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
                'authentication' => $this->has_o_auth ? 'oauth' : null,
            ])
                ->connect();
        } catch (AuthFailedException $e) {
            logger($e->getMessage(), ['mail_account_id' => $this->id]);
        }

        return null;
    }

    public function getImapClient(): ?Client
    {
        if (is_null($this->imapClient)) {
            $this->imapClient = $this->connect();
        }

        return $this->imapClient;
    }

    public function syncFolders(): array
    {
        $client = $this->getImapClient();

        if (! $client) {
            return [];
        }

        $folderIds = [];
        $folders = $client->getFolders($this->supportsHierarchicalFolders, soft_fail: true);

        foreach ($folders as $folder) {
            $folderIds = array_merge($folderIds, $this->syncFolder($folder));
        }

        resolve_static(MailFolder::class, 'query')
            ->whereKeyNot(array_values($folderIds))
            ->where('mail_account_id', $this->getKey())
            ->get('id')
            ->each(
                fn (MailFolder $folder) => DeleteMailFolder::make(['id' => $folder->getKey()])
                    ->validate()
                    ->execute()
            );

        return $folderIds;
    }

    public function mailer(): Mailer
    {
        $fromName = $this->smtp_from_name
            ?: auth()->user()?->name
            ?: config('mail.from.name');

        $config = [
            'transport' => $this->smtp_mailer,
            'username' => $this->smtp_user ?? $this->smtp_email,
            'password' => $this->smtp_password,
            'host' => $this->smtp_host,
            'port' => $this->smtp_port,
            'encryption' => $this->smtp_encryption,
            'from_address' => $this->smtp_email,
            'from_name' => $fromName,
        ];

        $mailer = Mail::build($config);

        $mailer->alwaysFrom($this->smtp_email, $fromName);

        if ($this->smtp_reply_to) {
            $mailer->alwaysReplyTo($this->smtp_reply_to);
        }

        return $mailer;
    }

    public function mailFolders(): HasMany
    {
        return $this->hasMany(MailFolder::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mail_account_user')
            ->withPivot('is_default');
    }

    public function mailMessages(): HasMany
    {
        return $this->hasMany(Communication::class);
    }

    protected function syncFolder(Folder $folder, ?int $parentId = null): array
    {
        $folderIds = [];
        $mailFolder = resolve_static(MailFolder::class, 'query')
            ->where('mail_account_id', $this->getKey())
            ->where('slug', $folder->path)
            ->first();

        $action = $mailFolder?->getKey()
            ? UpdateMailFolder::class
            : CreateMailFolder::class;

        $mailFolder = $action::make([
            'id' => $mailFolder?->getKey(),
            'mail_account_id' => $this->getKey(),
            'parent_id' => $parentId,
            'name' => $folder->name,
            'slug' => $folder->path,
        ])
            ->validate()
            ->execute();

        $folderIds[$folder->path] = $mailFolder->getKey();

        if ($folder->hasChildren()) {
            foreach ($folder->getChildren() as $child) {
                $folderIds = array_merge($folderIds, $this->syncFolder($child, $mailFolder->getKey()));
            }
        }

        return $folderIds;
    }
}
