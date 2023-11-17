<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailFolder extends Model
{
    use HasPackageFactory, HasUuid;

    protected $guarded = [
        'id',
    ];

    public function children(): HasMany
    {
        return $this->hasMany(MailFolder::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MailFolder::class, 'parent_id');
    }

    public function mailAccount(): BelongsTo
    {
        return $this->belongsTo(MailAccount::class);
    }

    public function mailMessages(): HasMany
    {
        return $this->hasMany(MailMessage::class);
    }
}
