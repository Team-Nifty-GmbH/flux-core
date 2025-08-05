<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\ContactIndustry;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Industry extends FluxModel
{
    use HasPackageFactory, SortableTrait;

    public array $sortable = [
        'sort_when_creating' => true,
    ];

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_industry')->using(ContactIndustry::class);
    }
}
