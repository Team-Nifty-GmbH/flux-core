<?php

namespace FluxErp\Support\Collection;

use FluxErp\Models\Address;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Blade;

class AddressCollection extends Collection
{
    public function toMap(): Collection
    {
        return $this->transform(function (Address $address) {
            return [
                'id' => $address->id,
                'tooltip' => '<div>'.implode(', ', $address->postal_address).'</div>',
                'popup' => '<a href="'.$address->detailRoute().'">'.__('Show').'</a>',
                'icon' => Blade::render(
                    'flux::components.address.map-marker',
                    ['img' => $address->getAvatarUrl()]
                ),
                'latitude' => $address->latitude,
                'longitude' => $address->longitude,
            ];
        });
    }
}
