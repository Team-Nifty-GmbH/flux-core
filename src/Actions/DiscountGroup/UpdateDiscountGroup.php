<?php

namespace FluxErp\Actions\DiscountGroup;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateDiscountGroupRequest;
use FluxErp\Models\DiscountGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateDiscountGroup implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateDiscountGroupRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'discount group.update';
    }

    public static function description(): string|null
    {
        return 'update discount-group';
    }

    public static function models(): array
    {
        return [DiscountGroup::class];
    }

    public function execute(): Model
    {
        $discountGroup = DiscountGroup::query()
            ->whereKey($this->data['id'])
            ->first();

        $discountGroup->fill($this->data);
        $discountGroup->save();

        return $discountGroup->withoutRelations()->fresh();
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
