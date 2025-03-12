<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\SerialNumber;
use Illuminate\Database\Eloquent\Builder;

class SerialNumberList extends BaseDataTable
{
    public array $enabledCols = [
        'id',
        'avatar',
        'product.name',
        'serial_number',
        'contacts',
    ];

    public array $formatters = [
        'avatar' => 'image',
    ];

    protected string $model = SerialNumber::class;

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with([
            'product:products.id,products.name',
            'product.media',
            'addresses:id,name,address_serial_number.quantity',
        ]);
    }

    protected function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);
        $returnArray['avatar'] = $item->product?->getAvatarUrl();
        $returnArray['contacts'] = $item->addresses->toSerialNumber()->pluck('quantity', 'name')->toArray() ?: null;

        return $returnArray;
    }
}
