<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SepaMandate extends Model
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $hidden = [
        'model_type',
    ];

    protected $casts = [
        'uuid' => 'string',
    ];

    protected $guarded = [
        'id',
    ];

    public function contactBankConnection(): BelongsTo
    {
        return $this->belongsTo(ContactBankConnection::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
