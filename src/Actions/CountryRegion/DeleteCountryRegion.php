<?php

namespace FluxErp\Actions\CountryRegion;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\CountryRegion;

class DeleteCountryRegion extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:country_regions,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [CountryRegion::class];
    }

    public function execute(): ?bool
    {
        return CountryRegion::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
