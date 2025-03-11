<?php

namespace FluxErp\Actions\CountryRegion;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CountryRegion;
use FluxErp\Rulesets\CountryRegion\UpdateCountryRegionRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateCountryRegion extends FluxAction
{
    public static function models(): array
    {
        return [CountryRegion::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateCountryRegionRuleset::class;
    }

    public function performAction(): Model
    {
        $countryRegion = resolve_static(CountryRegion::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $countryRegion->fill($this->data);
        $countryRegion->save();

        return $countryRegion->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(CountryRegion::class));

        $this->data = $validator->validate();
    }
}
