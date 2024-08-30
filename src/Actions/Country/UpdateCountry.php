<?php

namespace FluxErp\Actions\Country;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Country;
use FluxErp\Rulesets\Country\UpdateCountryRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UpdateCountry extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateCountryRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Country::class];
    }

    public function performAction(): Model
    {
        $country = resolve_static(Country::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $country->fill($this->data);
        $country->save();

        return $country->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->rules['iso_alpha2'] = $this->rules['iso_alpha2'] . ',' . ($this->data['id'] ?? 0);
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ($isoNumeric = data_get($this->data, 'iso_numeric')) {
            $numeric = bcadd(data_get($this->data, 'iso_numeric'), 0, 9);

            if (
                bccomp($numeric, bcfloor(data_get($this->data, 'iso_numeric'))) !== 0 || Str::contains($isoNumeric, '.')
            ) {
                throw ValidationException::withMessages([
                    'iso_numeric' => [__(':attribute cannot have decimal places', ['attribute' => 'iso_numeric'])],
                ]);
            }
        }
    }
}
