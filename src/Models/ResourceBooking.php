<?php

namespace FluxErp\Models;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\ResourceBooking\CreateResourceBooking;
use FluxErp\Actions\ResourceBooking\DeleteResourceBooking;
use FluxErp\Actions\ResourceBooking\UpdateResourceBooking;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class ResourceBooking extends FluxModel implements InteractsWithDataTables
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid, LogsActivity, Searchable, SoftDeletes;

    public static string $iconName = 'calendar-days';

    public static function fromResourceBooking(array $data, string $action): ?FluxAction
    {
        return match ($action) {
            'create' => CreateResourceBooking::make($data),
            'update' => UpdateResourceBooking::make($data),
            'delete' => DeleteResourceBooking::make($data),
            default => null,
        };
    }

    protected function casts(): array
    {
        return [
            'start' => 'datetime',
            'end' => 'datetime',
        ];
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo('assignable');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getLabel(): ?string
    {
        return $this->resource?->name;
    }

    public function getDescription(): ?string
    {
        return $this->start?->toDateTimeString() . ' – ' . $this->end?->toDateTimeString();
    }

    public function getUrl(): ?string
    {
        return $this->resource_id ? route('resources.id?', $this->resource_id) : null;
    }

    public function getAvatarUrl(): ?string
    {
        return null;
    }
}
