<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Country\CreateCountry;
use FluxErp\Actions\Country\DeleteCountry;
use FluxErp\Actions\Country\UpdateCountry;
use Livewire\Attributes\Locked;

class CountryForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $language_id = null;

    public ?int $currency_id = null;

    public ?string $name = null;

    public ?string $iso_alpha2 = null;

    public ?string $iso_alpha3 = null;

    public ?string $iso_numeric = null;

    public bool $is_active = true;

    public bool $is_default = false;

    public bool $is_eu_country = false;

    protected function getActions(): array
    {
        return [
            'create' => CreateCountry::class,
            'update' => UpdateCountry::class,
            'delete' => DeleteCountry::class,
        ];
    }
}
