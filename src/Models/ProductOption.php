<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\ProductProductOption;
use FluxErp\Support\Collection\ProductOptionCollection;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasAttributeTranslations;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductOption extends FluxModel
{
    use Filterable, HasAttributeTranslations, HasPackageFactory, HasUserModification, HasUuid, LogsActivity,
        SoftDeletes;

    // Relations
    public function productOptionGroup(): BelongsTo
    {
        return $this->belongsTo(ProductOptionGroup::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_product_option')
            ->using(ProductProductOption::class);
    }

    // Public methods
    public function newCollection(array $models = []): Collection
    {
        return app(ProductOptionCollection::class, ['items' => $models]);
    }

    // Protected methods
    protected function translatableAttributes(): array
    {
        return [
            'name',
        ];
    }
}
