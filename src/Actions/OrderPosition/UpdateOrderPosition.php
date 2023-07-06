<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Helpers\Helper;
use FluxErp\Http\Requests\UpdateOrderPositionRequest;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Price;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateOrderPosition implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateOrderPositionRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'order-position.update';
    }

    public static function description(): string|null
    {
        return 'update order position';
    }

    public static function models(): array
    {
        return [OrderPosition::class];
    }

    public function execute(): Model
    {
        $tags = Arr::pull($this->data, 'tags', []);

        $orderPosition = OrderPosition::query()
            ->whereKey($this->data['id'] ?? null)
            ->firstOrNew();

        $orderPosition->fill($this->data);
        PriceCalculation::fill($orderPosition, $this->data);
        unset($orderPosition->discounts);
        $orderPosition->save();

        $orderPosition->syncTags($tags);

        return $orderPosition->withoutRelations()->fresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        if ($this->data['id'] ?? false) {
            $errors = [];
            $orderPosition = OrderPosition::query()
                ->whereKey($this->data['id'])
                ->first();

            // Check if new parent causes a cycle
            if (($this->data['parent_id'] ?? false)
                && Helper::checkCycle(OrderPosition::class, $orderPosition, $this->data['parent_id'])
            ) {
                $errors += [
                    'parent_id' => [__('Cycle detected')],
                ];
            }

            if ($this->data['price_id'] ?? false) {
                // Check if the new price exists in the current price list

                if (Price::query()
                    ->whereKey($this->data['price_id'])
                    ->where(
                        'price_list_id',
                        '!=',
                        $this->data['price_list_id'] ?? $orderPosition->price_list_id
                    )
                    ->exists()
                ) {
                    $errors += [
                        'price_id' => [__('Price not found in price list')],
                    ];
                }
            }

            if ($errors) {
                throw ValidationException::withMessages($errors)->errorBag('updateOrderPosition');
            }
        }

        return $this;
    }
}
