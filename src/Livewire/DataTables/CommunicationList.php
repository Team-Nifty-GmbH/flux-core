<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Communication;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CommunicationList extends BaseDataTable
{
    public array $enabledCols = [
        'date',
        'from',
        'to',
        'subject',
        'text_body',
        'communication_type_enum',
    ];

    public array $formatters = [
        'total_time_ms' => 'time',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected string $model = Communication::class;

    public function getFormatters(): array
    {
        return array_merge(parent::getFormatters(), [
            'from' => 'email',
            'subject' => 'string',
            'text_body' => 'string',
            'communication_type_enum' => [
                'state',
                [
                    __('Letter') => 'gray',
                    __('Mail') => 'indigo',
                    __('Phone Call') => 'emerald',
                ],
            ],
        ]);
    }

    protected function augmentItemArray(array &$itemArray, Model $item): void
    {
        if (is_string($itemArray['communication_type_enum'] ?? null)) {
            $itemArray['communication_type_enum'] = __(Str::headline($itemArray['communication_type_enum']));
        }

        if (is_string($itemArray['text_body'] ?? null)) {
            $itemArray['text_body'] = Str::limit($itemArray['text_body'], 100);
        }
    }
}
