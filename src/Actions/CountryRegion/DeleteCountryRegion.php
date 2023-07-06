<?php

namespace FluxErp\Actions\CountryRegion;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\CountryRegion;
use Illuminate\Support\Facades\Validator;

class DeleteCountryRegion implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:country_regions,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'country-region.delete';
    }

    public static function description(): string|null
    {
        return 'delete country region';
    }

    public static function models(): array
    {
        return [CountryRegion::class];
    }

    public function execute(): bool|null
    {
        return CountryRegion::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
