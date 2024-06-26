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
use Illuminate\Support\Traits\Conditionable;

class PriceHelper
{
    use Conditionable;

    private ?Contact $contact = null;

    private ?Discount $discount = null;

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
        return app(static::class, ['product' => $product]);
    }

    public function setContact(Contact $contact): static
    {
        $this->contact = $contact;

        if (is_null($this->priceList)) {
            $this->priceList = $contact->priceList;
        }

        return $this;
    }

    public function addDiscount(Discount $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    public function setPriceList(PriceList $priceList): static
    {
        $this->priceList = $priceList;

        return $this;
    }

    public function setTimestamp(string $timestamp): static
    {
        $this->timestamp = Carbon::parse($timestamp)->toDateTimeString();

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

        if (! $price && $this->priceList?->parent) {
            $price = $this->calculatePriceFromPriceList($this->priceList, []);

            if ($price) {
                $price->isInherited = true;
                unset($price->id, $price->uuid);
            }
        }

        if (! $price) {
            $price = $this->contact?->priceList?->prices()
                ->where('product_id', $this->product->id)
                ->first();
        }

        if (! $price && $this->useDefault) {
            $price = app(Price::class)->query()
                ->where('product_id', $this->product->id)
                ->whereRelation('priceList', 'is_default', true)
                ->first();

            if ($price) {
                $price->isInherited = true;
                unset($price->id, $price->uuid);
            }
        } else {
            $price?->setRelation('priceList', $this->priceList);
        }

        if (! $price) {
            return null;
        }

        if ($price->isInherited) {
            $productCategoriesDiscounts = $price->priceList->categoryDiscounts()
                ->wherePivotIn('category_id', $this->product->categories()->pluck('id')->toArray())
                ->get();

            $this->calculateLowestDiscountedPrice($price, $productCategoriesDiscounts);
        }

        if ($this->contact) {
            $discounts = app(Discount::class)->query()
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
                            fn (Builder $query) => $query->where('model_type', app(Product::class)->getMorphClass())
                                ->where('model_id', $this->product->id))
                        ->orWhere(
                            fn (Builder $query) => $query->where('model_type', app(Category::class)->getMorphClass())
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
                                fn (Builder $query) => $query->where('model_type', app(Product::class)->getMorphClass())
                                    ->where('model_id', $this->product->id))
                            ->orWhere(
                                fn (Builder $query) => $query
                                    ->where('model_type', app(Category::class)->getMorphClass())
                                    ->whereIntegerInRaw(
                                        'model_id',
                                        $this->product->categories()->pluck('id')->toArray()
                                    )
                            );
                    })
                    ->get()
            );

            $this->calculateLowestDiscountedPrice($price, $discounts->diff($productCategoriesDiscounts ?? collect()));
        }

        // Apply added discount
        if ($this->discount) {
            $this->calculateLowestDiscountedPrice($price, collect($this->discount));
        }

        // Calculated total discounts based on base price and end price
        if ($price->basePrice) {
            // normalize base price to match net/gross with the returned price
            $function = $price->basePrice->priceList->is_net ? 'getNet' : 'getGross';
            $originalPrice = $price->basePrice->{$function}($this->product->vatRate?->rate_percentage);
            $price->basePrice->priceList->is_net = $this->priceList->is_net;
            $price->basePrice->price = $originalPrice;

            $price->discountFlat = bcsub($originalPrice, $price->price);
            $price->discountPercentage = $originalPrice != 0 ? diff_percentage($originalPrice, $price->price) : 0;
        }

        // set the used priceList
        if ($this->priceList) {
            $price->price_list_id = $this->priceList->id;

            $price->rootPrice = (app(Price::class))
                ->forceFill(
                    $this->getRootPrice($this->priceList, $price)?->toArray() ?? $price->toArray()
                );
        }

        if ($price->rootPrice) {
            // normalize root price to match net/gross with the returned price
            $function = $this->priceList->is_net ? 'getNet' : 'getGross';
            $rootPrice = $price->rootPrice->{$function}($this->product->vatRate?->rate_percentage);
            $price->rootPrice->priceList->is_net = $this->priceList->is_net;
            $price->rootPrice->price = $rootPrice;

            if (bccomp($price->price, 0) !== 0) {
                $price->rootDiscountPercentage = diff_percentage($rootPrice, $price->price);
            }
            $price->rootDiscountFlat = bcsub($rootPrice, $price->price);
        }

        return $price;
    }

    /**
     * Calculate price from price list based on price list parent(s) and discount per price list
     */
    protected function calculatePriceFromPriceList(PriceList $priceList, array $discounts): ?Price
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
                $price->basePrice = (app(Price::class))->forceFill($price->toArray());
            }

            $function = $this->priceList->is_net ? 'getNet' : 'getGross';
            $discountedPrice = $price->{$function}($this->product->vatRate?->rate_percentage);
            foreach (array_reverse($discounts) as $discount) {
                $discountedPrice = $discount->is_percentage ?
                    bcmul($discountedPrice, (1 - $discount->discount)) : bcsub($discountedPrice, $discount->discount);
                $price->appliedDiscounts[] = $discount;
            }

            $price->price = $discountedPrice;

            return $price;
        }

        if ($priceList->parent) {
            $price = $this->calculatePriceFromPriceList($priceList->parent, $discounts);
        }

        return $price;
    }

    protected function calculateLowestDiscountedPrice(Price $price, Collection $discounts): void
    {
        if (! $price->basePrice && $discounts->count()) {
            $price->basePrice = clone $price;
        }

        $maxPercentageDiscount = $discounts->reduce(function (?Discount $carry, Discount $item) {
            return $item->is_percentage && $item->discount > $carry?->discount ? $item : $carry;
        });

        $maxFlatDiscount = $discounts->reduce(function (?Discount $carry, Discount $item) {
            return ! $item->is_percentage && $item->discount > $carry?->discount ? $item : $carry;
        });

        $discountedPercentage = bcmul($price->price, (1 - ($maxPercentageDiscount->discount ?? 0)));
        $discountedFlat = bcsub($price->price, $maxFlatDiscount->discount ?? 0);

        if (bccomp($discountedPercentage, $discountedFlat) === -1) {
            $price->price = $discountedPercentage;
            if ($maxPercentageDiscount) {
                $price->appliedDiscounts[] = $maxPercentageDiscount;
            }
        } else {
            $price->price = $discountedFlat;
            if ($maxFlatDiscount) {
                $price->appliedDiscounts[] = $maxFlatDiscount;
            }
        }
    }

    protected function getRootPrice(?PriceList $priceList, ?Price $price): ?Price
    {
        if (is_null($priceList)) {
            return $price;
        }

        $parentPrice = $priceList->parent?->prices()
            ->where('product_id', $this->product->id)
            ->first();

        if ($priceList->parent) {
            $price = $this->getRootPrice($priceList->parent, $parentPrice);
        }

        return $parentPrice ?? $price;
    }
}
