<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class VatRate extends Model
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $casts = [
        'uuid' => 'string',
    ];

    protected $guarded = [
        'id',
    ];

    public function getNameAttribute(string $name): string
    {
        return $name . ' ' . format_number(bcmul($this->rate_percentage, 100)) . '%';
    }
}
