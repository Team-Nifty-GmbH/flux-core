<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Discount\CreateDiscount;
use FluxErp\Actions\Discount\UpdateDiscount;
use FluxErp\Actions\PriceList\CreatePriceList;
use FluxErp\Actions\PriceList\DeletePriceList;
use FluxErp\Actions\PriceList\UpdatePriceList;
use FluxErp\Models\Category;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use WireUi\Traits\Actions;

class PriceLists extends Component
{
    use Actions;

    public array $selectedPriceList = [
        'name' => '',
        'parent_id' => null,
        'price_list_code' => null,
        'is_net' => false,
        'is_default' => false,
        'discount' => [
            'discount' => null,
            'is_percentage' => true,
        ],
    ];

    public array $priceLists;

    public array $categories;

    public array $discountedCategories = [];

    public array $newCategoryDiscount = [
        'category_id' => null,
        'discount' => null,
        'is_percentage' => true,
    ];

    public bool $editModal = false;

    public function mount(): void
    {
        $this->priceLists = PriceList::query()
            ->get()
            ->toArray();

        $this->categories = Category::query()
            ->where('model_type', Product::class)
            ->get(['id', 'name'])
            ->toArray();
    }

    public function showEditModal(int $priceListId = null): void
    {
        $priceList = PriceList::query()
            ->whereKey($priceListId)
            ->with('discount')
            ->first();

        $this->selectedPriceList = $priceList?->toArray() ?: [
            'name' => '',
            'parent_id' => null,
            'price_list_code' => null,
            'is_net' => false,
            'is_default' => false,
            'discount' => [
                'discount' => null,
                'is_percentage' => true,
            ],
        ];

        if (is_null($this->selectedPriceList['discount'])) {
            $this->selectedPriceList['discount'] = [
                'discount' => null,
                'is_percentage' => true,
            ];
        }

        if ($priceList) {
            $this->discountedCategories = $priceList->discountedCategories()
                ->where('model_type', Product::class)
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

            if ($this->selectedPriceList['discount']['is_percentage']
                && $this->selectedPriceList['discount']['discount']
            ) {
                $this->selectedPriceList['discount']['discount'] *= 100;
            }
        } else {
            $this->discountedCategories = [];

            $this->newCategoryDiscount = [
                'category_id' => null,
                'discount' => null,
                'is_percentage' => true,
            ];
        }

        $this->editModal = true;
    }

    public function save(): void
    {
        $action = ($this->selectedPriceList['id'] ?? false) ? UpdatePriceList::class : CreatePriceList::class;
        $selectedPriceList = $this->selectedPriceList;

        if ($selectedPriceList['discount']['is_percentage']
            && $selectedPriceList['discount']['discount']
        ) {
            $selectedPriceList['discount']['discount'] /= 100;
        }

        try {
            $priceList = $action::make($selectedPriceList)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        // Create product category discounts
        $categories = $priceList->discountedCategories()
            ->where('model_type', Product::class)
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
                                'model_type' => Category::class,
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

        array_unshift($this->priceLists, $priceList->toArray());

        $this->notification()->success(__('Successfully saved'));
        $this->editModal = false;

        $this->dispatch('loadData')->to('data-tables.price-list-list');
        $this->skipRender();
    }

    public function delete(): void
    {
        try {
            DeletePriceList::make(['id' => $this->selectedPriceList['id']])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $index = array_search($this->selectedPriceList['id'], array_column($this->priceLists, 'id'));
        unset($this->priceLists[$index]);
        $this->dispatch('loadData')->to('data-tables.price-list-list');
    }

    public function addCategoryDiscount(): void
    {
        $this->skipRender();

        if ($this->newCategoryDiscount['category_id'] === null || ! $this->newCategoryDiscount['discount']) {
            return;
        }

        $this->discountedCategories[] = [
            'id' => $this->newCategoryDiscount['category_id'],
            'name' => Category::query()
                ->whereKey($this->newCategoryDiscount['category_id'])
                ->first(['name'])
                ->name,
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

    public function removeCategoryDiscount(int $index): void
    {
        unset($this->discountedCategories[$index]);

        $this->skipRender();
    }

    public function render(): View
    {
        return view('flux::livewire.settings.price-lists');
    }
}
