<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\SerialNumber;
use Illuminate\Database\Eloquent\Builder;

class SerialNumberList extends BaseDataTable
{
    protected string $model = SerialNumber::class;

    public array $enabledCols = [
        'id',
        'avatar',
        'product.name',
        'serial_number',
        'customer',
    ];

    public array $formatters = [
        'avatar' => 'image',
    ];

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with([
            'product:id,name',
            'product.media',
            'address:id,firstname,lastname,company',
        ]);
    }

    protected function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);
        $returnArray['avatar'] = $item->product?->getAvatarUrl();
        $returnArray['customer'] = $item->address?->getLabel();

        return $returnArray;
    }
}
