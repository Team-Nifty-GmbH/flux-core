<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Log;
use Illuminate\Support\Str;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class LogList extends BaseDataTable
{
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
                '100' => 'gray',
                '200' => 'indigo',
                '250' => 'indigo',
                '300' => 'amber',
                '400' => 'red',
                '500' => 'red',
                '550' => 'red',
                '600' => 'red',
            ],
        ],
        'level_name' => [
            'state',
            [
                'DEBUG' => 'gray',
                'INFO' => 'indigo',
                'NOTICE' => 'indigo',
                'WARNING' => 'amber',
                'ERROR' => 'red',
                'CRITICAL' => 'red',
                'ALERT' => 'red',
                'EMERGENCY' => 'red',
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

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Done'))
                ->icon('check')
                ->color('emerald')
                ->attributes([
                    'x-on:click' => 'event.stopPropagation(); $wire.markAsDone(record.id)',
                ]),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make(label: __('Mark found as done'))
                ->color('indigo')
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
