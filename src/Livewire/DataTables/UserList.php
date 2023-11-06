<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;

class UserList extends DataTable
{
    protected string $model = User::class;

    public array $enabledCols = [
        'id',
        'name',
        'email',
    ];

    public array $availableRelations = ['*'];

    public array $sortable = ['*'];

    public array $aggregatable = ['*'];

    public array $availableCols = ['*'];

    public array $formatters = [
        'avatar' => 'image',
    ];

    public array $leftAppend = [
        'name' => 'avatar',
    ];

    public array $bottomAppend = [
        'name' => 'email',
    ];

    public function getLeftAppends(): array
    {
        return [
            'name' => 'avatar',
        ];
    }

    public function getBottomAppends(): array
    {
        return [
            'name' => 'email',
        ];
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->with('media');
    }

    public function getReturnKeys(): array
    {
        $returnKeys = parent::getReturnKeys();
        $returnKeys[] = 'avatar';

        return $returnKeys;
    }

    public function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);
        $returnArray['avatar'] = $item->getAvatarUrl();

        return $returnArray;
    }
}
