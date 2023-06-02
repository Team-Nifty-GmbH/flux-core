<?php

namespace FluxErp\Http\Livewire\DataTables;

use FluxErp\Models\Log;
use Illuminate\Support\Str;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class LogList extends DataTable
{
    use HasEloquentListeners;

    protected string $model = Log::class;

    public array $enabledCols = [
        'id',
        'message',
        'level_name',
        'created_at',
    ];

    public ?bool $isSearchable = true;

    public array $formatters = [
        'message' => 'string',
        'created_at' => 'datetime',
        'level' => [
            'state',
            [
                '100' => 'secondary',
                '200' => 'primary',
                '250' => 'primary',
                '300' => 'warning',
                '400' => 'negative',
                '500' => 'negative',
                '550' => 'negative',
                '600' => 'negative',
            ],
        ],
        'level_name' => [
            'state',
            [
                'DEBUG' => 'secondary',
                'INFO' => 'primary',
                'NOTICE' => 'primary',
                'WARNING' => 'warning',
                'ERROR' => 'negative',
                'CRITICAL' => 'negative',
                'ALERT' => 'negative',
                'EMERGENCY' => 'negative',
            ],
        ],
    ];

    public function itemToArray($item): array
    {
        $item = parent::itemToArray($item);

        $item['message'] = Str::limit($item['message'], 150);

        return $item;
    }

    public function updatedSearch(): void
    {
        $this->filters = [[
            'message',
            'like',
            '%' . $this->search . '%',
        ]];

        parent::updatedSearch();
    }
}
