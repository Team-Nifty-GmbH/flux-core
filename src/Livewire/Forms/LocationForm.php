<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Location\CreateLocation;
use FluxErp\Actions\Location\DeleteLocation;
use FluxErp\Actions\Location\UpdateLocation;
use FluxErp\Models\Location;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class LocationForm extends FluxForm
{
    use SupportsAutoRender;

    public ?string $city = null;

    public ?int $client_id = null;

    public ?int $country_id = null;

    public ?int $country_region_id = null;

    public ?string $house_number = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public ?float $latitude = null;

    public ?float $longitude = null;

    public ?string $name = null;

    public ?string $street = null;

    public ?string $zip = null;

    protected static function getModel(): string
    {
        return Location::class;
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateLocation::class,
            'update' => UpdateLocation::class,
            'delete' => DeleteLocation::class,
        ];
    }
}
