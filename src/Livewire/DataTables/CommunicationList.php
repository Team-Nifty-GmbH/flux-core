<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Communication;
use Illuminate\Support\Str;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class CommunicationList extends BaseDataTable
{
    use HasEloquentListeners;

    protected string $model = Communication::class;

    public array $enabledCols = [
        'date',
        'from',
        'subject',
        'text_body',
        'communication_type_enum',
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

    public function itemToArray($item): array
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
