<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\AbsencePolicy;

class AbsencePolicyList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'max_consecutive_days',
        'min_notice_days',
        'requires_substitute',
        'requires_documentation',
        'documentation_after_days',
        'is_active',
    ];

    protected string $model = AbsencePolicy::class;

    public function getFormatters(): array
    {
        return [
            'requires_substitute' => function ($value) {
                return $value ? __('Yes') : __('No');
            },
            'requires_documentation' => function ($value) {
                return $value ? __('Yes') : __('No');
            },
            'is_active' => function ($value) {
                return $value ? __('Yes') : __('No');
            },
        ];
    }
}
