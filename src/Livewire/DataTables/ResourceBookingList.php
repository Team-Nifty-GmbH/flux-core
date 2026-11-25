<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\ResourceBooking;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;

class ResourceBookingList extends BaseDataTable
{
    #[Locked]
    public ?int $resourceId = null;

    public array $enabledCols = [
        'resource.name',
        'start',
        'end',
        'order.order_number',
        'description',
    ];

    protected string $model = ResourceBooking::class;

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder
            ->with(['resource:id,name', 'order:id,order_number'])
            ->when($this->resourceId, fn (Builder $query) => $query->where('resource_id', $this->resourceId));
    }
}
