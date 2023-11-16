<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\MailMessage;
use Illuminate\Support\Str;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class MailMessageList extends DataTable
{
    use HasEloquentListeners;

    protected string $model = MailMessage::class;

    public array $enabledCols = [
        'from',
        'subject',
        'text_body',
    ];

    public function getFormatters(): array
    {
        return array_merge(parent::getFormatters(), [
            'from' => 'email',
            'subject' => 'string',
            'text_body' => 'string',
        ]);
    }

    public function itemToArray($item): array
    {
        $array = parent::itemToArray($item);

        if ($array['text_body'] ?? false) {
            $array['text_body'] = Str::limit($array['text_body'], 100);
        }

        return $array;
    }
}
