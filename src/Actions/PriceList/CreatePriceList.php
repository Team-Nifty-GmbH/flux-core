<?php

namespace FluxErp\Actions\PriceList;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreatePriceListRequest;
use FluxErp\Models\PriceList;
use Illuminate\Support\Facades\Validator;

class CreatePriceList implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreatePriceListRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'price-list.create';
    }

    public static function description(): string|null
    {
        return 'create price list';
    }

    public static function models(): array
    {
        return [PriceList::class];
    }

    public function execute(): PriceList
    {
        $priceList = new PriceList($this->data);
        $priceList->save();

        return $priceList;
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
