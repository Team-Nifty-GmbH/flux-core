<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Address;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ContactList extends DataTable
{
    protected string $model = Address::class;

    public bool $showFilterInputs = true;

    public array $enabledCols = [
        'avatar',
        'contact.customer_number',
        'is_main_address',
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

    public function mount(): void
    {
        $attributes = ModelInfo::forModel(Address::class)->attributes;

        $this->availableCols = $attributes
            ->pluck('name')
            ->toArray();

        parent::mount();
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus'),
        ];
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

    public function getFilterableColumns(string $name = null): array
    {
        return $this->availableCols;
    }
}
