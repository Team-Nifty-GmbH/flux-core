<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Communication;
use Illuminate\Support\Str;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class CommunicationList extends DataTable
{
    use HasEloquentListeners;

    protected string $model = Communication::class;

    public array $enabledCols = [
        'date',
        'from',
        'subject',
        'text_body',
        'communication_type',
    ];

    public function getFormatters(): array
    {
        return array_merge(parent::getFormatters(), [
            'from' => 'email',
            'subject' => 'string',
            'text_body' => 'string',
            'communication_type' => [
                'state',
                [
                    __('Letter') => 'secondary',
                    __('Mail') => 'primary',
                    __('Phone Call') => 'positive',
                ],
            ],
        ]);
    }

    public function itemToArray($item): array
    {
        $array = parent::itemToArray($item);

        if ($array['communication_type'] ?? false) {
            $array['communication_type'] = __(Str::headline($array['communication_type']));
        }

        if ($array['text_body'] ?? false) {
            $array['text_body'] = Str::limit($array['text_body'], 100);
        }

        return $array;
    }
}
