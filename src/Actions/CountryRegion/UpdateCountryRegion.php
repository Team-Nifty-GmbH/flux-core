<?php

namespace FluxErp\Actions\CountryRegion;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateCountryRegionRequest;
use FluxErp\Models\CountryRegion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateCountryRegion extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateCountryRegionRequest())->rules();
    }

    public static function models(): array
    {
        return [CountryRegion::class];
    }

    public function execute(): Model
    {
        $countryRegion = CountryRegion::query()
            ->whereKey($this->data['id'])
            ->first();

        $countryRegion->fill($this->data);
        $countryRegion->save();

        return $countryRegion->withoutRelations()->fresh();
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new CountryRegion());

        $this->data = $validator->validate();

        return $this;
    }
}
