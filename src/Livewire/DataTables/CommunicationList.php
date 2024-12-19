<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Communication;
use Illuminate\Support\Str;

class CommunicationList extends BaseDataTable
{
    protected string $model = Communication::class;

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

    public function getFormatters(): array
    {
        return array_merge(parent::getFormatters(), [
            'from' => 'email',
            'subject' => 'string',
            'text_body' => 'string',
            'communication_type_enum' => [
                'state',
                [
                    __('Letter') => 'secondary',
                    __('Mail') => 'primary',
                    __('Phone Call') => 'positive',
                ],
            ],
        ]);
    }

    protected function itemToArray($item): array
    {
        $array = parent::itemToArray($item);

        if ($array['communication_type_enum'] ?? false) {
            $array['communication_type_enum'] = __(Str::headline($array['communication_type_enum']));
        }

        if ($array['text_body'] ?? false) {
            $array['text_body'] = Str::limit($array['text_body'], 100);
        }

        return $array;
    }
}
