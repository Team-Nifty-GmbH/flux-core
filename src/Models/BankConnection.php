<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BankConnection extends Model
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid;

    protected $casts = [
        'uuid' => 'string',
        'is_active' => 'boolean',
    ];

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::saving(function (BankConnection $model) {
            if ($model->isDirty('iban') && ! is_null($model->iban)) {
                $model->iban = str_replace(' ', '', strtoupper($model->iban));
            }
        });
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'bank_connection_client');
    }
}
