<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Livewire\DataTables\BaseDataTable;
use FluxErp\Models\FixedAssetDepreciation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
            'Booked Elsewhere' => 'emerald',
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
            ->withExists([
                'ledgerBookings as is_booked',
                'fixedAsset as is_externally_depreciated' => fn (Builder $query): Builder => resolve_static(
                    FixedAssetDepreciation::class,
                    'externallyDepreciated',
                    [$query]
                ),
            ])
            ->where('fixed_asset_depreciations.fixed_asset_id', $this->fixedAssetId);
    }

    protected function augmentItemArray(array &$itemArray, Model $item): void
    {
        $itemArray['status'] = match (true) {
            $item->is_booked => 'Booked',
            $item->is_externally_depreciated => 'Booked Elsewhere',
            $item->booking_date->lte(today()) => 'Due',
            default => 'Open',
        };
    }
}
