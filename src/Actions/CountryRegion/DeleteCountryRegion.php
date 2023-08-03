<?php

namespace FluxErp\Actions\CountryRegion;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CountryRegion;

class DeleteCountryRegion extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:country_regions,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [CountryRegion::class];
    }

    public function performAction(): ?bool
    {
        return CountryRegion::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
