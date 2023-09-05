<?php

namespace FluxErp\Helpers;

use Carbon\Carbon;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Models\Discount;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PriceHelper
{
    private ?Contact $contact = null;

    private ?PriceList $priceList = null;

    private Product $product;

    private string $timestamp;

    private bool $useDefault = true;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public static function make(Product $product): static
    {
        return new static($product);
    }

    public function setContact(Contact $contact): static
    {
        $this->contact = $contact;

        if(is_null($this->priceList)) {
            $this->priceList = $contact->priceList;
        }

        return $this;
    }

    public function setTimestamp(string $timestamp): static
    {
        $this->timestamp = Carbon::parse($timestamp)->toDateTimeString();

        return $this;
    }

    public function setPriceList(PriceList $priceList): static
    {
        $this->priceList = $priceList;

        return $this;
    }

    public function useDefault(bool $useDefault): static
    {
        $this->useDefault = $useDefault;

        return $this;
    }

    public function price(): ?Price
    {
        $this->timestamp = $this->timestamp ?? Carbon::now()->toDateTimeString();

        $price = $this->priceList?->prices()
            ->where('product_id', $this->product->id)
            ->first();
        $originalPrice = $price;

        if (! $price && $this->priceList?->parent) {
            $price = $this->calculatePriceFromPriceList($this->priceList, []);
        }

        if (! $price) {
            $price = $this->contact?->priceList?->prices()
                ->where('product_id', $this->product->id)
                ->first();
        }

        if (! $price && $this->useDefault) {
            $price = Price::query()
                ->where('product_id', $this->product->id)
                ->whereRelation('priceList', 'is_default', true)
                ->first();
        } else {
            $price?->setRelation('priceList', $this->priceList);
        }

        if (! $price) {
            return null;
        }

        $productCategoriesDiscounts = $price->priceList->categoryDiscounts()
            ->wherePivotIn('category_id', $this->product->categories()->pluck('id')->toArray())
            ->get();

        $this->calculateLowestDiscountedPrice($price, $productCategoriesDiscounts);

        if ($this->contact) {
            $discounts = Discount::query()
                ->join('discount_discount_group AS ddg', 'discounts.id', 'ddg.discount_id')
                ->join('contact_discount_group AS cdg', 'ddg.discount_group_id', '=', 'cdg.discount_group_id')
                ->where('cdg.contact_id', $this->contact->id)
                ->where(function (Builder $query) {
                    return $query
                        ->where(fn (Builder $query) => $query
                            ->where('from', '<=', $this->timestamp)
                            ->where('till', '>=', $this->timestamp)
                        )
                        ->orWhere(fn (Builder $query) => $query
                            ->where('from', '<=', $this->timestamp)
                            ->whereNull('till')
                        )
                        ->orWhere(fn (Builder $query) => $query
                            ->where('till', '>=', $this->timestamp)
                            ->whereNull('from')
                        )
                        ->orWhere(fn (Builder $query) => $query
                            ->whereNull('from')
                            ->whereNull('till')
                        );
                })
                ->where(function (Builder $query) {
                    return $query
                        ->where(
                            fn (Builder $query) => $query->where('model_type', Product::class)
                                ->where('model_id', $this->product->id))
                        ->orWhere(
                            fn (Builder $query) => $query->where('model_type', Category::class)
                                ->whereIntegerInRaw(
                                    'model_id',
                                    $this->product->categories()->pluck('id')->toArray()
                                )
                        );
                })
                ->get();

            $discounts = $discounts->merge(
                $this->contact->discounts()
                    ->where(function (Builder $query) {
                        return $query
                            ->where(fn (Builder $query) => $query
                                ->where('from', '<=', $this->timestamp)
                                ->where('till', '>=', $this->timestamp)
                            )
                            ->orWhere(fn (Builder $query) => $query
                                ->where('from', '<=', $this->timestamp)
                                ->whereNull('till')
                            )
                            ->orWhere(fn (Builder $query) => $query
                                ->where('till', '>=', $this->timestamp)
                                ->whereNull('from')
                            )
                            ->orWhere(fn (Builder $query) => $query
                                ->whereNull('from')
                                ->whereNull('till')
                            );
                    })
                    ->where(function (Builder $query) {
                        return $query
                            ->where(
                                fn (Builder $query) => $query->where('model_type', Product::class)
                                    ->where('model_id', $this->product->id))
                            ->orWhere(
                                fn (Builder $query) => $query->where('model_type', Category::class)
                                    ->whereIntegerInRaw(
                                        'model_id',
                                        $this->product->categories()->pluck('id')->toArray()
                                    )
                            );
                    })
                    ->get()
            );

            $this->calculateLowestDiscountedPrice($price, $discounts->diff($productCategoriesDiscounts));
        }

        if (is_null($originalPrice)) {
            $price->isCalculated = true;
        }

        return $price;
    }

    /**
     * Calculate price from price list based on price list parent(s) and discount per price list
     */
    private function calculatePriceFromPriceList(PriceList $priceList, array $discounts): ?Price
    {
        $discounts[] = $priceList->discount()
            ->where(function (Builder $query) {
                return $query
                    ->where(fn (Builder $query) => $query
                        ->where('from', '<=', $this->timestamp)
                        ->where('till', '>=', $this->timestamp)
                    )
                    ->orWhere(fn (Builder $query) => $query
                        ->where('from', '<=', $this->timestamp)
                        ->whereNull('till')
                    )
                    ->orWhere(fn (Builder $query) => $query
                        ->where('till', '>=', $this->timestamp)
                        ->whereNull('from')
                    )
                    ->orWhere(fn (Builder $query) => $query
                        ->whereNull('from')
                        ->whereNull('till')
                    );
            })
            ->first();

        $price = $priceList->parent?->prices()
            ->where('product_id', $this->product->id)
            ->first();

        // If price was found, apply all the discounts in reverse order
        if ($price) {
            $discounts = array_filter($discounts);
            if ($discounts) {
                $price->basePrice = (new Price())->forceFill($price->toArray());
            }

            $function = $this->priceList->is_net ? 'getNet' : 'getGross';
            $discountedPrice = $price->{$function}($this->product->vatRate?->rate_percentage);
            foreach (array_reverse($discounts) as $discount) {
                $discountedPrice = $discount->is_percentage ?
                    bcmul($discountedPrice, (1 - $discount->discount)) : bcsub($discountedPrice, $discount->discount);
            }

            $price->price = $discountedPrice;

            return $price;
        }

        if ($priceList->parent) {
            $price = $this->calculatePriceFromPriceList($priceList->parent, $discounts);
        }

        return $price;
    }

    private function calculateLowestDiscountedPrice(Price $price, Collection $discounts): void
    {
        if (! $price->basePrice && $discounts->count()) {
            $price->basePrice = (new Price())->forceFill($price->toArray());
        }

        $maxPercentageDiscount = $discounts->max(fn ($item) => $item->is_percentage ? $item->discount : 0);
        $maxFlatDiscount = $discounts->max(fn ($item) => $item->is_percentage ? 0 : $item->discount) ?: 0;

        $discountedPercentage = bcmul($price->price, (1 - $maxPercentageDiscount));
        $discountedFlat = bcsub($price->price, $maxFlatDiscount);

        $price->price = bccomp($discountedPercentage, $discountedFlat) === -1 ?
            $discountedPercentage : $discountedFlat;
    }
}
