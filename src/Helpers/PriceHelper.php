<?php

namespace FluxErp\Helpers;

use Carbon\Carbon;
use FluxErp\Enums\RoundingMethodEnum;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Models\Discount;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Support\Calculation\Rounding;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Conditionable;
use InvalidArgumentException;

class PriceHelper
{
    use Conditionable;

    public ?Price $price = null;

    private ?Contact $contact = null;

    private ?Discount $discount = null;

    private ?PriceList $priceList = null;

    private Product $product;

    private string $timestamp;

    private bool $useDefault = true;

    public function __construct(Product $product)
    {
        if (! $product->getKey()) {
            throw new InvalidArgumentException('Product must have an id');
        }

        $product->loadMissing(['categories:id', 'vatRate:id,rate_percentage']);
        $this->product = $product;
    }

    public static function make(Product $product): static
    {
        return app(static::class, ['product' => $product]);
    }

    public function addDiscount(Discount $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function getPriceList(): ?PriceList
    {
        return $this->priceList;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getTimestamp(): ?string
    {
        return $this->timestamp;
    }

    public function price(): ?Price
    {
        $this->timestamp = $this->timestamp ?? Carbon::now()->toDateTimeString();

        $this->price = $this->priceList?->prices()
            ->where('product_id', $this->product->getKey())
            ->first();

        if (! $this->price && $this->priceList?->parent) {
            $this->price = $this->calculatePriceFromPriceList($this->priceList, []);

            if ($this->price) {
                $this->price->isInherited = true;
                unset($this->price->id, $this->price->uuid);
            }
        }

        if (! $this->price) {
            $this->price = $this->contact?->priceList?->prices()
                ->where('product_id', $this->product->getKey())
                ->first();
        }

        if (! $this->price && $this->useDefault) {
            $this->priceList = resolve_static(PriceList::class, 'default');
            $this->price = resolve_static(Price::class, 'query')
                ->where('product_id', $this->product->getKey())
                ->whereRelation('priceList', 'is_default', true)
                ->first();

            if ($this->price) {
                $this->price->isInherited = true;
                unset($this->price->id, $this->price->uuid);
            }
        } else {
            $this->price?->setRelation('priceList', $this->priceList);
        }

        if (! $this->price) {
            return null;
        }

        if ($this->price->isInherited) {
            $productCategoriesDiscounts = $this->price->priceList->categoryDiscounts()
                ->wherePivotIn('category_id', $this->product->categories()->pluck('id')->toArray())
                ->get();

            $this->calculateLowestDiscountedPrice($this->price, $productCategoriesDiscounts);
        }

        if ($this->contact) {
            $discounts = resolve_static(Discount::class, 'query')
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
                                ->where('model_id', $this->product->getKey()))
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
                                fn (Builder $query) => $query->where('model_type', morph_alias(Product::class))
                                    ->where('model_id', $this->product->getKey()))
                            ->orWhere(
                                fn (Builder $query) => $query
                                    ->where('model_type', morph_alias(Category::class))
                                    ->whereIntegerInRaw(
                                        'model_id',
                                        $this->product->categories()->pluck('id')->toArray()
                                    )
                            );
                    })
                    ->get()
            );

            $this->calculateLowestDiscountedPrice(
                $this->price,
                $discounts->diff($productCategoriesDiscounts ?? collect())
            );
        }

        // Apply added discount
        if ($this->discount) {
            $this->calculateLowestDiscountedPrice($this->price, collect([$this->discount]));
        }

        $this->fireEvent('price.calculated');

        // set the used priceList and eventually round the price
        if ($this->priceList) {
            $this->price->price_list_id = $this->priceList->id;

            $this->price->rootPrice = (app(Price::class))
                ->forceFill(
                    $this->getRootPrice(
                        $this->price->basePrice?->priceList ?? $this->priceList,
                        $this->price
                    )?->toArray() ?? $this->price->toArray()
                );

            $this->price->price = match ($this->priceList->rounding_method_enum) {
                RoundingMethodEnum::Round => Rounding::round($this->price->price, $this->priceList->rounding_precision),
                RoundingMethodEnum::Ceil => Rounding::ceil($this->price->price, $this->priceList->rounding_precision),
                RoundingMethodEnum::Floor => Rounding::floor($this->price->price, $this->priceList->rounding_precision),
                RoundingMethodEnum::Nearest => Rounding::nearest(
                    number: $this->priceList->rounding_number,
                    value: $this->price->price,
                    precision: $this->priceList->rounding_precision,
                    mode: $this->priceList->rounding_mode
                ),
                RoundingMethodEnum::End => Rounding::end(
                    number: $this->priceList->rounding_number,
                    value: $this->price->price,
                    precision: $this->priceList->rounding_precision,
                    mode: $this->priceList->rounding_mode
                ),
                default => $this->price->price
            };

            $this->fireEvent('price.rounded');
        }

        // Calculated total discounts based on base price and end price
        if ($this->price->basePrice) {
            // normalize base price to match net/gross with the returned price
            $function = $this->price->basePrice->priceList->is_net ? 'getNet' : 'getGross';
            $originalPrice = $this->price->basePrice->{$function}($this->product->vatRate?->rate_percentage);
            $this->price->basePrice->priceList->is_net = $this->priceList->is_net;
            $this->price->basePrice->price = $originalPrice;

            $this->price->discountFlat = bcsub($originalPrice, $this->price->price);
            $this->price->discountPercentage = $originalPrice != 0
                ? diff_percentage($originalPrice, $this->price->price)
                : 0;
        }

        if ($this->price->rootPrice) {
            // normalize root price to match net/gross with the returned price
            $function = $this->priceList->is_net ? 'getNet' : 'getGross';
            $rootPrice = $this->price->rootPrice->{$function}($this->product->vatRate?->rate_percentage);
            $this->price->rootPrice->priceList->is_net = $this->priceList->is_net;
            $this->price->rootPrice->price = $rootPrice;

            if (bccomp($this->price->price, 0) !== 0) {
                $this->price->rootDiscountPercentage = $rootPrice != 0
                    ? diff_percentage($rootPrice, $this->price->price)
                    : '0';
            }

            $this->price->rootDiscountFlat = bcsub($rootPrice, $this->price->price);
        }

        return $this->price;
    }

    public function setContact(Contact $contact): static
    {
        $this->contact = $contact;

        if (is_null($this->priceList)) {
            $this->priceList = $contact->priceList;
        }

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

    protected function calculateLowestDiscountedPrice(Price $price, Collection $discounts): void
    {
        if (! $price->basePrice && $discounts->count()) {
            $price->basePrice = clone $price;
        }

        $maxPercentageDiscount = $discounts->reduce(function (?Discount $carry, Discount $item) {
            return ($item->is_percentage && $item->discount > $carry?->discount) ? $item : $carry;
        });

        $maxFlatDiscount = $discounts->reduce(function (?Discount $carry, Discount $item) {
            return (! $item->is_percentage && $item->discount > $carry?->discount) ? $item : $carry;
        });

        $discountedPercentage = bcmul($price->price, (1 - ($maxPercentageDiscount->discount ?? 0)));
        $discountedFlat = bcsub($price->price, $maxFlatDiscount->discount ?? 0);

        if (! $this->contact && $discounts->count() === 1) {
            $price->price = $discounts->first()->is_percentage ? $discountedPercentage : $discountedFlat;
            $price->appliedDiscounts = $discounts->all();

            return;
        }

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
            ->where('product_id', $this->product->getKey())
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

    protected function fireEvent(string $event): void
    {
        event($event, $this);
    }

    protected function getRootPrice(?PriceList $priceList, ?Price $price): ?Price
    {
        if (is_null($priceList)) {
            return $price;
        }

        $parentPrice = $priceList->parent?->prices()
            ->where('product_id', $this->product->getKey())
            ->first();

        if ($priceList->parent) {
            $price = $this->getRootPrice($priceList->parent, $parentPrice);
        }

        return $parentPrice ?? $price;
    }
}
