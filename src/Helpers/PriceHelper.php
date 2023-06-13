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

class PriceHelper
{
    private Contact|null $contact = null;

    private PriceList|null $priceList = null;

    private Product $product;

    private string $timestamp;

    private bool $useDefault = true;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public static function make(Product $product): static
    {
        return (new static($product));
    }

    public function setContact(Contact $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function setTimestamp(string $timestamp): self
    {
        $this->timestamp = Carbon::parse($timestamp)->toDateTimeString();

        return $this;
    }

    public function setPriceList(PriceList $priceList): self
    {
        $this->priceList = $priceList;

        return $this;
    }

    public function useDefault(bool $useDefault): self
    {
        $this->useDefault = $useDefault;

        return $this;
    }

    public function price(): Price|null
    {
        $this->timestamp = $this->timestamp ?? Carbon::now()->toDateTimeString();

        $price = $this->priceList?->prices()
            ->where('product_id', $this->product->id)
            ->first();

        if (! $price && $this->priceList->parent) {
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
        }

        if (! $price) {
            return null;
        }

        $price->setRelation('priceList', $this->priceList);

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

            $maxPercentageDiscount = $discounts->max(fn ($item) => $item->is_percentage ? $item->discount : 0);
            $maxFlatDiscount = $discounts->max(fn ($item) => $item->is_percentage ? 0 : $item->discount);

            $discountedPercentage = bcmul($price->price,  (1 - $maxPercentageDiscount));
            $discountedFlat = bcsub($price->price, $maxFlatDiscount);

            $price->price = bccomp($discountedPercentage, $discountedFlat) === -1 ?
                $discountedPercentage : $discountedFlat;
        }

        return $price;
    }


    /**
     * Calculate price from price list based on price list parent(s) and discount per price list
     */
    private function calculatePriceFromPriceList(PriceList $priceList, array $discounts): Price|null
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
            $discountedPrice = $price->price;
            foreach (array_reverse(array_filter($discounts)) as $discount) {
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
}
