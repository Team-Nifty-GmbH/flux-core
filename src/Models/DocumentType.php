<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @deprecated
 */
class DocumentType extends FluxModel
{
    use Filterable, HasClientAssignment, HasPackageFactory, HasUserModification, HasUuid, LogsActivity,
        SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function documentGenerationSettings(): HasMany
    {
        return $this->hasMany(DocumentGenerationSetting::class, 'document_type_id');
    }

    public function paymentNotices(): HasMany
    {
        return $this->hasMany(PaymentNotice::class, 'document_type_id');
    }
}
