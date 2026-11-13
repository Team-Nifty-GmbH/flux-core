<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Livewire\DataTables\BaseDataTable;
use FluxErp\Models\FixedAssetDepreciation;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class FixedAssetDepreciations extends BaseDataTable
{
    #[Modelable]
    public ?int $fixedAssetId = null;

    public array $columnLabels = [
        'sequence' => 'Sequence',
        'book_value_after' => 'Book Value',
        'status' => 'Status',
    ];

    public array $enabledCols = [
        'sequence',
        'booking_date',
        'amount',
        'book_value_after',
        'status',
    ];

    public bool $orderAsc = true;

    public string $orderBy = 'sequence';

    public array $formatters = [
        'booking_date' => 'date',
        'amount' => 'money',
        'book_value_after' => 'money',
        'status' => ['badge', [
            'Booked' => 'green',
            'Due' => 'amber',
            'Open' => 'gray',
        ]],
    ];

    public array $sortable = [
        'sequence',
        'booking_date',
    ];

    protected string $model = FixedAssetDepreciation::class;

    public function getBuilder(Builder $builder): Builder
    {
        return $builder
            ->select('fixed_asset_depreciations.*')
            ->selectRaw(
                'case
                    when exists (
                        select 1 from ledger_bookings lb
                        where lb.source_type = ?
                          and lb.source_id = fixed_asset_depreciations.id
                          and lb.deleted_at is null
                    ) then ?
                    when fixed_asset_depreciations.booking_date <= ? then ?
                    else ?
                end as status',
                [
                    morph_alias(FixedAssetDepreciation::class),
                    'Booked',
                    now()->toDateString(),
                    'Due',
                    'Open',
                ]
            )
            ->where('fixed_asset_depreciations.fixed_asset_id', $this->fixedAssetId);
    }
}
