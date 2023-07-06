<?php

namespace FluxErp\Actions\Product;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateProductRequest;
use FluxErp\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CreateProduct implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateProductRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'product.create';
    }

    public static function description(): string|null
    {
        return 'create product';
    }

    public static function models(): array
    {
        return [Product::class];
    }

    public function execute(): Product
    {
        $productOptions = Arr::pull($this->data, 'product_options', []);
        $productProperties = Arr::mapWithKeys(
            Arr::pull($this->data, 'product_properties', []),
            fn ($item, $key) => [$item['id'] => $item['value']]
        );
        $bundleProducts = Arr::pull($this->data, 'bundle_products', false);
        $prices = Arr::pull($this->data, 'prices', false);
        $tags = Arr::pull($this->data, 'tags', []);

        $product = new Product($this->data);
        $product->save();

        $product->attachTags($tags);
        $product->productOptions()->attach($productOptions);
        $product->productProperties()->attach($productProperties);
        $product->prices()->createMany($this->data['prices'] ?? []);

        if ($product->is_bundle && $bundleProducts) {
            $product->bundleProducts()
                ->sync(
                    collect($bundleProducts)
                        ->unique('id')
                        ->mapWithKeys(fn ($item) => [$item['id'] => ['count' => $item['count']]])
                        ->toArray()
                );
        }

        if ($prices) {
            $product->prices()->createMany($prices);
        }

        return $product->refresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Product());

        $this->data = $validator->validate();

        return $this;
    }
}
