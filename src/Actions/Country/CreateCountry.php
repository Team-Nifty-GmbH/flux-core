<?php

namespace FluxErp\Actions\Country;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Country;
use FluxErp\Rulesets\Country\CreateCountryRuleset;
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

    protected function prepareForValidation(): void
    {
        $this->data['iso_numeric'] = data_get($this->data, 'iso_numeric')
            ? Str::of(data_get($this->data, 'iso_numeric'))->padLeft(3, '0')->toString()
            : null;
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (Str::contains(data_get($this->data, 'iso_numeric', ''), '.')) {
            throw ValidationException::withMessages([
                'iso_numeric' => [__('validation.no_decimals', ['attribute' => 'iso_numeric'])],
            ]);
        }
    }
}
