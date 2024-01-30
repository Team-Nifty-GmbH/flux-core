<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTranslations;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use Filterable, HasClientAssignment, HasPackageFactory, HasTranslations, HasUserModification, HasUuid, SoftDeletes;

    protected $casts = [
        'uuid' => 'string',
        'is_active' => 'boolean',
    ];

    protected $guarded = [
        'id',
    ];

    public $translatable = [
        'name',
        'description',
        'additional_header',
        'additional_footer',
    ];

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
