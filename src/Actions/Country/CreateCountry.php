<?php

namespace FluxErp\Actions\Country;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Country;
use FluxErp\Rulesets\Country\CreateCountryRuleset;
use Illuminate\Support\Facades\Validator;

class CreateCountry extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateCountryRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Country::class];
    }

    public function performAction(): Country
    {
        $country = app(Country::class, ['attributes' => $this->data]);
        $country->save();

        return $country->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(Country::class));

        $this->data = $validator->validate();
    }
}
