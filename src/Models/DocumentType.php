<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTranslations;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @deprecated
 */
class DocumentType extends Model
{
    use Filterable, HasClientAssignment, HasPackageFactory, HasTranslations, HasUuid, LogsActivity, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    public $translatable = [
        'name',
        'description',
        'additional_header',
        'additional_footer',
    ];

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
