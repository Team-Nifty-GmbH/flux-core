<?php

namespace FluxErp\Actions\CountryRegion;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateCountryRegionRequest;
use FluxErp\Models\CountryRegion;
use Illuminate\Support\Facades\Validator;

class CreateCountryRegion extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateCountryRegionRequest())->rules();
    }

    public static function models(): array
    {
        return [CountryRegion::class];
    }

    public function performAction(): CountryRegion
    {
        $countryRegion = new CountryRegion($this->data);
        $countryRegion->save();

        return $countryRegion->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new CountryRegion());

        $this->data = $validator->validate();
    }
}
