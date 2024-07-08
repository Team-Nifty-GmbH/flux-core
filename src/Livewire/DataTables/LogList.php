<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Log;
use Illuminate\Support\Str;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class LogList extends BaseDataTable
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

    public function mount(): void
    {
        parent::mount();

        if (! $this->userFilters) {
            $this->userFilters = [
                [
                    [
                        'column' => 'is_done',
                        'operator' => '=',
                        'value' => false,
                    ],
                ],
            ];
        }
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make(label: __('Done'))
                ->icon('check')
                ->color('positive')
                ->attributes([
                    'x-on:click' => 'event.stopPropagation(); $wire.markAsDone(record.id)',
                ]),
        ];
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make(label: __('Mark found as done'))
                ->color('primary')
                ->attributes([
                    'x-on:click' => '$wire.markAllAsDone()',
                ]),
        ];
    }

    protected function itemToArray($item): array
    {
        $item = parent::itemToArray($item);

        $item['message'] = Str::limit($item['message'], 150);

        return $item;
    }

    public function startSearch(): void
    {
        $this->filters = [[
            'message',
            'like',
            '%' . $this->search . '%',
        ]];

        parent::startSearch();
    }

    public function markAsDone(Log $log): void
    {
        $this->skipRender();

        $log->is_done = true;
        $log->save();

        $this->loadData();
    }

    public function markAllAsDone(): void
    {
        $this->skipRender();

        $this->buildSearch()->update(['is_done' => true]);

        $this->loadData();
    }
}
