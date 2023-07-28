<?php

namespace FluxErp\Actions\Country;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateCountryRequest;
use FluxErp\Models\Country;
use Illuminate\Support\Facades\Validator;

class CreateCountry extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateCountryRequest())->rules();
    }

    public static function models(): array
    {
        return [Country::class];
    }

    public function performAction(): Country
    {
        $country = new Country($this->data);
        $country->save();

        return $country;
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Country());

        $this->data = $validator->validate();
    }
}
