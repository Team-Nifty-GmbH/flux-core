<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactBankConnection extends FluxModel
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::saving(function (ContactBankConnection $model) {
            if ($model->isDirty('iban')) {
                $model->iban = str_replace(' ', '', strtoupper($model->iban));
            }
        });
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function sepaMandates(): HasMany
    {
        return $this->hasMany(SepaMandate::class);
    }
}
