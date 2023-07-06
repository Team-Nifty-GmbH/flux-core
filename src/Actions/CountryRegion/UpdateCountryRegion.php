<?php

namespace FluxErp\Actions\CountryRegion;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateCountryRegionRequest;
use FluxErp\Models\CountryRegion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateCountryRegion implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateCountryRegionRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'country-region.update';
    }

    public static function description(): string|null
    {
        return 'update country region';
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

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new CountryRegion());

        $this->data = $validator->validate();

        return $this;
    }
}
