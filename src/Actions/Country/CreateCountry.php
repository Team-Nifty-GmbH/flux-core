<?php

namespace FluxErp\Actions\Country;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateCountryRequest;
use FluxErp\Models\Country;
use Illuminate\Support\Facades\Validator;

class CreateCountry implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateCountryRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'country.create';
    }

    public static function description(): string|null
    {
        return 'create country';
    }

    public static function models(): array
    {
        return [Country::class];
    }

    public function execute(): Country
    {
        $country = new Country($this->data);
        $country->save();

        return $country;
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
