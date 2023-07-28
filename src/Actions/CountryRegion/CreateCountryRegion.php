<?php

namespace FluxErp\Actions\CountryRegion;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateCountryRegionRequest;
use FluxErp\Models\CountryRegion;
use Illuminate\Support\Facades\Validator;

class CreateCountryRegion extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateCountryRegionRequest())->rules();
    }

    public static function models(): array
    {
        return [CountryRegion::class];
    }

    public function execute(): CountryRegion
    {
        $countryRegion = new CountryRegion($this->data);
        $countryRegion->save();

        return $countryRegion->fresh();
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new CountryRegion());

        $this->data = $validator->validate();

        return $this;
    }
}
