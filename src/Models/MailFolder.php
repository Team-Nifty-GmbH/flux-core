<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasParentChildRelations;
use FluxErp\Traits\HasUuid;
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
}
