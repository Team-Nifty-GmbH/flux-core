<?php

namespace FluxErp\Actions\CountryRegion;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateCountryRegionRequest;
use FluxErp\Models\CountryRegion;
use Illuminate\Support\Facades\Validator;

class CreateCountryRegion implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateCountryRegionRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'country-region.create';
    }

    public static function description(): string|null
    {
        return 'create country region';
    }

    public static function models(): array
    {
        return [CountryRegion::class];
    }

    public function execute(): CountryRegion
    {
        $countryRegion = new CountryRegion($this->data);
        $countryRegion->save();

        return $countryRegion;
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
