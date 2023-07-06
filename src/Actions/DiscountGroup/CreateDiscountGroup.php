<?php

namespace FluxErp\Actions\DiscountGroup;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateDiscountGroupRequest;
use FluxErp\Models\DiscountGroup;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CreateDiscountGroup implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateDiscountGroupRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'discount-group.create';
    }

    public static function description(): string|null
    {
        return 'create discount group';
    }

    public static function models(): array
    {
        return [DiscountGroup::class];
    }

    public function execute(): DiscountGroup
    {
        $discounts = Arr::pull($this->data, 'discounts', []);

        $discountGroup = new DiscountGroup($this->data);
        $discountGroup->save();

        if ($discounts) {
            $discountGroup->discounts()->attach($discounts);
        }

        return $discountGroup;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
