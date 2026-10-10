<?php

namespace FluxErp\Models;

use FluxErp\Mail\ImapMessageBuilder;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasParentChildRelations;
use FluxErp\Traits\Model\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailFolder extends FluxModel
{
    use HasPackageFactory, HasParentChildRelations, HasUuid;

    protected static function booted(): void
    {
        static::saved(function (MailFolder $mailFolder): void {
            if (! $mailFolder->is_sent || ! $mailFolder->wasChanged('is_sent') && ! $mailFolder->wasRecentlyCreated) {
                return;
            }

            // A mail account keeps its sent mails in exactly one folder.
            resolve_static(MailFolder::class, 'query')
                ->where('mail_account_id', $mailFolder->mail_account_id)
                ->whereKeyNot($mailFolder->getKey())
                ->where('is_sent', true)
                ->update(['is_sent' => false]);
        });
    }

    protected function casts(): array
    {
        return [
            'is_sent' => 'boolean',
        ];
    }

    // Relations
    public function mailAccount(): BelongsTo
    {
        return $this->belongsTo(MailAccount::class);
    }

    public function mailMessages(): HasMany
    {
        return $this->hasMany(Communication::class);
    }

    // Public methods
    public function messages(): ImapMessageBuilder
    {
        return app(ImapMessageBuilder::class, ['folder' => $this]);
    }
}
