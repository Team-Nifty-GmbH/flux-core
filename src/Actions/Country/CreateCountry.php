<?php

namespace FluxErp\Actions\Country;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateCountryRequest;
use FluxErp\Models\Country;
use Illuminate\Support\Facades\Validator;

class CreateCountry extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateCountryRequest())->rules();
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

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Country());

        $this->data = $validator->validate();

        return $this;
    }
}
