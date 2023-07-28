<?php

namespace FluxErp\Actions\Country;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateCountryRequest;
use FluxErp\Models\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateCountry extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateCountryRequest())->rules();
    }

    public static function models(): array
    {
        return [Country::class];
    }

    public function performAction(): Model
    {
        $country = Country::query()
            ->whereKey($this->data['id'])
            ->first();

        $country->fill($this->data);
        $country->save();

        return $country->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Country());

        $this->data = $validator->validate();
    }
}
