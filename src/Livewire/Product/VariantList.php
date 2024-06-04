<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Actions\Product\CreateProduct;
use FluxErp\Actions\Product\DeleteProduct;
use FluxErp\Actions\Product\Variant\CreateVariants;
use FluxErp\Livewire\DataTables\ProductList;
use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Models\Product;
use FluxErp\Models\ProductOptionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class VariantList extends ProductList
{
    protected string $view = 'flux::livewire.product.variant-list';

    public array $enabledCols = [
        'name',
        'product_number',
        'product_options.name',
        'is_active',
    ];

    #[Modelable]
    public ProductForm $product;

    public array $productOptions = [];

    public array $selectedOptions = [];

    public array $variants = [];

    public function mount(): void
    {
        $this->filters = [
            [
                'parent_id',
                '=',
                $this->product->id,
            ],
        ];

        $groups = app(ProductOptionGroup::class)->query()
            ->pluck('id')
            ->toArray();

        $this->selectedOptions = array_fill_keys($groups, []);

        app(Product::class)->query()
            ->whereKey($this->product->id)
            ->with('children:id,parent_id')
            ->first()
            ->children
            ?->each(function ($item) {
                $item->productOptions->each(function ($item) {
                    $this->selectedOptions[$item->product_option_group_id][] = $item->id;
                });
            });

        $this->selectedOptions = array_map(fn ($item) => array_values(array_unique($item)), $this->selectedOptions);

        parent::mount();
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('Edit Variants'))
                ->icon('pencil')
                ->attributes([
                    'x-on:click' => <<<'JS'
                        $openModal('generate-variants-modal')
                    JS,
                ])
                ->when(
                    fn () => resolve_static(CreateProduct::class, 'canPerformAction', [false])
                        && resolve_static(DeleteProduct::class, 'canPerformAction', [false])
                ),
        ];
    }

    public function loadOptions(ProductOptionGroup $optionGroup): void
    {
        $this->productOptions = $optionGroup
            ->productOptions()
            ->select(['id', 'product_option_group_id', 'name'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_option_group_id' => $item->product_option_group_id,
                    'name' => $item->name,
                ];
            })
            ->toArray();
    }

    public function next(): void
    {
        $activeProductOptionCombinations = Arr::crossJoin(...array_values(array_filter($this->selectedOptions)));

        $this->variants = [];
        foreach ($activeProductOptionCombinations as $activeProductOption) {
            if (! $product = app(Product::class)->query()
                ->where('parent_id', $this->product->id)
                ->whereHas('productOptions', function (Builder $query) use ($activeProductOption) {
                    return $query
                        ->select('product_product_option.product_id')
                        ->whereIntegerInRaw('product_options.id', $activeProductOption)
                        ->groupBy('product_product_option.product_id')
                        ->havingRaw('COUNT(`product_options`.`id`) = ?', [count($activeProductOption)]);
                })
                ->first()
            ) {
                $this->variants['new'][] = $activeProductOption;
            } else {
                $this->variants['existing'][] = $product->id;
            }
        }

        $this->variants['delete'] = app(Product::class)->query()
            ->select('id')
            ->where('parent_id', $this->product->id)
            ->whereNotIn('id', $this->variants['existing'] ?? [])
            ->get()
            ->toArray();
    }

    public function save(): void
    {
        foreach (data_get($this->variants, 'delete', []) as $variantDelete) {
            DeleteProduct::make($variantDelete)
                ->checkPermission()
                ->validate()
                ->execute();
        }

        try {
            CreateVariants::make(
                array_merge(
                    $this->product->toArray(),
                    [
                        'parent_id' => $this->product->id,
                        'product_options' => data_get($this->variants, 'new', []),
                    ]
                )
            )
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }

        $this->variants = [];
        $this->loadData();
    }
}
