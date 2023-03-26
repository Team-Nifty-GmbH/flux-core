<?php

namespace FluxErp\Http\Livewire\DataTables;

use FluxErp\Models\SerialNumber;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class SerialNumberList extends DataTable
{
    protected string $model = SerialNumber::class;

    public array $enabledCols = [
        'id',
        'avatar',
        'product.name',
        'serial_number',
        'customer',
    ];

    public array $availableRelations = ['*'];

    public array $sortable = ['*'];

    public array $formatters = [
        'avatar' => 'image',
    ];

    public function mount(): void
    {
        $attributes = ModelInfo::forModel(SerialNumber::class)->attributes;

        $this->availableCols = $attributes
            ->pluck('name')
            ->toArray();

        parent::mount();
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->with([
            'product:id,name',
            'product.media',
            'address:id,firstname,lastname,company',
        ]);
    }

    public function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);
        $returnArray['avatar'] = $item->product?->getAvatarUrl();
        $returnArray['customer'] = $item->address?->getLabel();

        return $returnArray;
    }
}
