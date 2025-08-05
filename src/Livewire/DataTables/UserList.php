<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserList extends BaseDataTable
{
    public array $bottomAppend = [
        'name' => 'email',
    ];

    public array $enabledCols = [
        'id',
        'name',
        'email',
    ];

    public array $formatters = [
        'avatar' => 'image',
    ];

    public array $leftAppend = [
        'name' => 'avatar',
    ];

    protected string $model = User::class;

    protected function getBottomAppends(): array
    {
        return [
            'name' => 'email',
        ];
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with('media');
    }

    protected function getLeftAppends(): array
    {
        return [
            'name' => 'avatar',
        ];
    }

    protected function getReturnKeys(): array
    {
        $returnKeys = parent::getReturnKeys();
        $returnKeys[] = 'avatar';

        return $returnKeys;
    }

    protected function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);
        $returnArray['avatar'] = $item->getAvatarUrl();

        return $returnArray;
    }
}
