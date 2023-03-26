<?php

namespace FluxErp\Http\Livewire\DataTables;

use FluxErp\Models\Address;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class ContactList extends DataTable
{
    protected string $model = Address::class;

    public array $enabledCols = [
        'avatar',
        'contact.customer_number',
        'company',
        'firstname',
        'lastname',
        'street',
        'zip',
        'city',
    ];

    public array $sortable = ['*'];

    public array $availableRelations = ['*'];

    public array $formatters = [
        'avatar' => 'image',
    ];

    public array $filters = [
        'where' => [
            'is_main_address',
            '=',
            true,
        ],
    ];

    public function mount(): void
    {
        $attributes = ModelInfo::forModel(Address::class)->attributes;

        $this->availableCols = $attributes
            ->pluck('name')
            ->toArray();

        parent::mount();
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->with('contact.media');
    }

    public function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);
        $returnArray['avatar'] = $item->getAvatarUrl();

        return $returnArray;
    }
}
