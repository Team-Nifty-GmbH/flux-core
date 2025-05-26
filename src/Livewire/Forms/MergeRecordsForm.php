<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Record\MergeRecords;
use Livewire\Attributes\Locked;

class MergeRecordsForm extends FluxForm
{
    public array $main_record = [
        'id' => null,
        'columns' => [],
    ];

    public array $merge_records = [];

    #[Locked]
    public ?string $model_type = null;

    protected function getActions(): array
    {
        return [
            'create' => MergeRecords::class,
        ];
    }
}
