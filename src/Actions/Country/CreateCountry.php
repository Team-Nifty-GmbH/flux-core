<?php

namespace FluxErp\Actions\Country;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Country;
use FluxErp\Rulesets\Country\CreateCountryRuleset;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
