<?php

namespace FluxErp\Actions\ProductProperty;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\ProductProperty;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeleteProductProperty implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:product_properties,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'product-property.delete';
    }

    public static function description(): string|null
    {
        return 'delete product property';
    }

    public static function models(): array
    {
        return [ProductProperty::class];
    }

    public function execute()
    {
        return ProductProperty::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        if (ProductProperty::query()
                ->whereKey($this->data['id'])
                ->first()
                ->products()
                ->count() > 0
        ) {
            throw ValidationException::withMessages([
                'products' => [__('Product property has products')],
            ])->errorBag('deleteProductProperty');
        }

        return $this;
    }
}
