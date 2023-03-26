<?php

namespace FluxErp\Http\Livewire\DataTables;

use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class ProductList extends DataTable
{
    protected string $model = Product::class;

    public array $enabledCols = [
        'id',
        'product_image',
        'product_number',
        'name',
    ];

    public array $availableRelations = ['*'];

    public array $sortable = ['*'];

    public array $formatters = [
        'product_image' => 'image',
    ];

    public function mount(): void
    {
        $attributes = ModelInfo::forModel(Product::class)->attributes;

        $this->availableCols = $attributes
            ->pluck('name')
            ->toArray();

        parent::mount();
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->with('media');
    }

    public function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);
        $returnArray['product_image'] = $item->getAvatarUrl();

        return $returnArray;
    }
}
