<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\ContactIndustry;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\SoftDeletes;
use FluxErp\Traits\Model\SortableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Industry extends FluxModel
{
    use HasPackageFactory, HasUserModification, SoftDeletes, SortableTrait;

    public array $sortable = [
        'sort_when_creating' => true,
    ];

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_industry')
            ->using(ContactIndustry::class);
    }
}
