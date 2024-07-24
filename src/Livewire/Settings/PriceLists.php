<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Discount\CreateDiscount;
use FluxErp\Actions\Discount\UpdateDiscount;
use FluxErp\Actions\PriceList\CreatePriceList;
use FluxErp\Actions\PriceList\UpdatePriceList;
use FluxErp\Enums\RoundingMethodEnum;
use FluxErp\Livewire\DataTables\PriceListList;
use FluxErp\Livewire\Forms\PriceListForm;
use FluxErp\Models\Category;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class PriceLists extends PriceListList
{
    use Actions;

    protected ?string $includeBefore = 'flux::livewire.settings.price-lists';

    public PriceListForm $priceList;

    public array $discountedCategories = [];

    public array $newCategoryDiscount = [
        'category_id' => null,
        'discount' => null,
        'is_percentage' => true,
    ];

    public function mount(): void
    {
        parent::mount();

        $this->headline = __('Price Lists');
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('New'))
                ->icon('plus')
                ->color('primary')
                ->when(resolve_static(CreatePriceList::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit',
                ]),
        ];
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->when(resolve_static(UpdatePriceList::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
        ];
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'priceLists' => resolve_static(PriceList::class, 'query')
                    ->get(['id', 'name'])
                    ->toArray(),
                'roundingMethods' => RoundingMethodEnum::valuesLocalized(),
                'roundingModes' => [
                    'round' => __('Round'),
                    'ceil' => __('Round up'),
                    'floor' => __('Round down'),
                ],
            ]
        );
    }

    #[Renderless]
    public function edit(PriceList $priceList): void
    {
        $this->priceList->reset();
        $this->priceList->fill($priceList);

        if ($this->priceList->id) {
            $this->discountedCategories = $priceList->discountedCategories()
                ->where('model_type', app(Product::class)->getMorphClass())
                ->orderBy('sort_number', 'DESC')
                ->with([
                    'discounts' => fn ($query) => $query->where('category_price_list.price_list_id', $priceList->id)
                        ->select(['id', 'discount', 'is_percentage']),
                ])
                ->get()
                ->map(function ($item) {
                    if (($item->discounts[0] ?? false) && $item->discounts[0]->is_percentage) {
                        $item->discounts[0]->discount *= 100;
                    }

                    return $item;
                })
                ->toArray();
        } else {
            $this->discountedCategories = [];

            $this->newCategoryDiscount = [
                'category_id' => null,
                'discount' => null,
                'is_percentage' => true,
            ];
        }

        $this->js(<<<'JS'
            $openModal('edit-price-list');
        JS);
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->priceList->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $priceList = $this->priceList->getModelInstance();

        // Create product category discounts
        $categories = $priceList->discountedCategories()
            ->where('model_type', app(Product::class)->getMorphClass())
            ->orderBy('sort_number', 'DESC')
            ->with([
                'discounts' => fn ($query) => $query->where('category_price_list.price_list_id', $priceList->id)
                    ->select(['id', 'discount', 'is_percentage']),
            ])
            ->get()
            ->toArray();

        $categoryIds = array_column($categories, 'id');

        foreach ($this->discountedCategories as $discountedCategory) {
            if ($discountedCategory['discounts'][0]['is_percentage']
                && $discountedCategory['discounts'][0]['discount']
            ) {
                $discountedCategory['discounts'][0]['discount'] /= 100;
            }

            // Update category discount if category already discounted else create category discount
            if (($index = array_search($discountedCategory['id'], $categoryIds)) !== false) {
                try {
                    UpdateDiscount::make($discountedCategory['discounts'][0])
                        ->checkPermission()
                        ->validate()
                        ->execute();
                } catch (\Exception) {
                    continue;
                }

                $categories[$index]['exists'] = true;
            } else {
                try {
                    $discount = CreateDiscount::make(
                        array_merge(
                            $discountedCategory['discounts'][0],
                            [
                                'model_type' => app(Category::class)->getMorphClass(),
                                'model_id' => $discountedCategory['id'],
                            ]
                        )
                    )
                        ->checkPermission()
                        ->validate()
                        ->execute();
                } catch (\Exception) {
                    continue;
                }

                $priceList->discountedCategories()->attach($discountedCategory['id'], ['discount_id' => $discount->id]);
                $categories[] = array_merge($discountedCategory, ['exists' => true]);
            }
        }

        // Delete removed discounted categories
        if ($removed = array_filter($categories, fn ($item) => ! ($item['exists'] ?? false))) {
            $priceList->discountedCategories()->detach(array_column($removed, 'id'));
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function delete(): bool
    {
        try {
            $this->priceList->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function addCategoryDiscount(): void
    {
        if ($this->newCategoryDiscount['category_id'] === null || ! $this->newCategoryDiscount['discount']) {
            return;
        }

        $this->discountedCategories[] = [
            'id' => $this->newCategoryDiscount['category_id'],
            'name' => resolve_static(Category::class, 'query')
                ->whereKey($this->newCategoryDiscount['category_id'])
                ->value('name'),
            'discounts' => [
                $this->newCategoryDiscount,
            ],
        ];

        $this->newCategoryDiscount = [
            'category_id' => null,
            'discount' => null,
            'is_percentage' => true,
        ];
    }

    #[Renderless]
    public function removeCategoryDiscount(int $index): void
    {
        unset($this->discountedCategories[$index]);
    }
}
