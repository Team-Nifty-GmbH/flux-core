<?php

namespace FluxErp\Livewire\DataTables;



use FluxErp\Models\TicketType;

class TicketTypesList extends BaseDataTable
{

    public ?bool $isSearchable = true;

    protected string $model = TicketType::class;


    public array $enabledCols = [
        'name',
        'model_type',
        'additional_columns.name',
        'additional_columns.validations',
        'additional_columns.values',
    ];
}
