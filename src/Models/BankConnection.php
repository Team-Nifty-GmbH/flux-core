<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BankConnection extends FluxModel
{
    use Filterable, HasClientAssignment, HasPackageFactory, HasUserModification, HasUuid, LogsActivity;

    protected static function booted(): void
    {
        static::saving(function (BankConnection $model): void {
            if ($model->isDirty('iban') && ! is_null($model->iban)) {
                $model->iban = str_replace(' ', '', strtoupper($model->iban));
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_virtual' => 'boolean',
        ];
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'bank_connection_client');
    }
}
