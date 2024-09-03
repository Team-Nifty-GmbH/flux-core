<?php

namespace FluxErp\Actions\Country;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Country;
use FluxErp\Rulesets\Country\UpdateCountryRuleset;
use Illuminate\Database\Eloquent\Model;
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
