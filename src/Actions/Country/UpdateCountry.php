<?php

namespace FluxErp\Actions\Country;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateCountryRequest;
use FluxErp\Models\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateCountry implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateCountryRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'country.update';
    }

    public static function description(): string|null
    {
        return 'update country';
    }

    public static function models(): array
    {
        return [Country::class];
    }

    public function execute(): Model
    {
        $country = Country::query()
            ->whereKey($this->data['id'])
            ->first();

        $country->fill($this->data);
        $country->save();

        return $country->withoutRelations()->fresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Country());

        $this->data = $validator->validate();

        return $this;
    }
}
