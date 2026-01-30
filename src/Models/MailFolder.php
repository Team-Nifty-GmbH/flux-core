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

    public function mailAccount(): BelongsTo
    {
        return $this->belongsTo(MailAccount::class);
    }

    public function mailMessages(): HasMany
    {
        return $this->hasMany(Communication::class);
    }

    public function messages(): ImapMessageBuilder
    {
        return new ImapMessageBuilder($this);
    }
}
