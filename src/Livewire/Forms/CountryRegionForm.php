<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\CountryRegion\CreateCountryRegion;
use FluxErp\Actions\CountryRegion\DeleteCountryRegion;
use FluxErp\Actions\CountryRegion\UpdateCountryRegion;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;

class CountryRegionForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $country_id = null;

    public ?string $name = null;

    public function getActions(): array
    {
        return [
            'create' => CreateCountryRegion::class,
            'update' => UpdateCountryRegion::class,
            'delete' => DeleteCountryRegion::class,
        ];
    }

    public function modalName(): ?string
    {
        return Str::kebab(class_basename($this)) . '-modal';
    }
}
