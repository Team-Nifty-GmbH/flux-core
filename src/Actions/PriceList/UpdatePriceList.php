<?php

namespace FluxErp\Actions\PriceList;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Helpers\Helper;
use FluxErp\Http\Requests\UpdatePriceListRequest;
use FluxErp\Models\PriceList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdatePriceList implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdatePriceListRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'price-list.update';
    }

    public static function description(): string|null
    {
        return 'update price list';
    }

    public static function models(): array
    {
        return [PriceList::class];
    }

    public function execute(): Model
    {
        $priceList = PriceList::query()
            ->whereKey($this->data['id'])
            ->first();

        $priceList->fill($this->data);
        $priceList->save();

        return $priceList->withoutRelations()->fresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        // Check if new parent causes a cycle
        if (
            ($this->data['parent_id'] ?? false)
            && Helper::checkCycle(
                model: PriceList::class,
                item: PriceList::query()->whereKey($this->data['id'])->first(),
                parentId: $this->data['parent_id']
            )
        ) {
            throw ValidationException::withMessages([
                'parent_id' => [__('Cycle detected')],
            ])->errorBag('updatePriceList');
        }

        return $this;
    }
}
